<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionControlShift1Resource\Pages;
use App\Models\ProductionControlShift1;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Customer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\HtmlString;

class ProductionControlShift1Resource extends Resource
{
    protected static ?string $model = ProductionControlShift1::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Production Control Sheet';
    protected static ?string $modelLabel = 'Production Control Sheet';
    protected static ?string $pluralModelLabel = 'Production Control Sheets';

    /**
     * Schema jam kerja shift (time slot per jam)
     */
    protected static function getTimeListSchema(?string $workHours = null, ?string $shift = null): array
    {
        // Jika user belum memilih `work_hours` atau `select_shift`, tunjukkan placeholder
        if (empty($workHours) || empty($shift)) {
            return [
                Grid::make()
                    ->schema([
                          Placeholder::make('warning')
                    ->label('')
                    ->content(new HtmlString('
                        <div style="text-align:center; color:#dc2626;">

                            <div style="
                                width:56px;
                                height:56px;
                                border:3px solid #dc2626;
                                border-radius:50%;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                font-size:32px;
                                font-weight:bold;
                                margin:0 auto 12px;
                            ">
                                !
                            </div>

                            <div style="font-size:18px; font-weight:700;">
                                PERHATIAN !!!
                            </div>

                            <div style="margin-top:6px;">
                                Silakan pilih <b>Shift</b> dan <b>Work Hours</b> terlebih dahulu untuk menampilkan form.
                            </div>

                        </div>
                    '))
                    ])
                    ->columns(1),
            ];
        }

        // pilih model timeslot berdasarkan pilihan workHours dan shift
        $shift = $shift ?? '1';
        $timeSlotClass = $workHours === '5 Hours'
            ? '\\App\\Models\\TimeSlotShift' . $shift . '_5h'
            : '\\App\\Models\\TimeSlotShift' . $shift . '_7h';

        $components = [];

        // jika kelas timeslot tidak ada, beri pesan agar developer menambahkannya
        if (! class_exists($timeSlotClass)) {
            $components[] = Grid::make()
                ->schema([
                    Placeholder::make('no_timeslot')->content(
                        "Database tabel '{$workHours}'. tidak ditemukan Pastikan model {$timeSlotClass} dibuat dan migration/seed dijalankan."
                    ),
                ])
                ->columns(1);

            return $components;
        }

        $timeSlots = $timeSlotClass::orderBy('order')->get();

        // jika tidak ada records timeslot, tampilkan informasi agar di-seed
        if ($timeSlots->isEmpty()) {
            $components[] = Grid::make()
                ->schema([
                    Placeholder::make('no_timeslot_data')->content(
                        "Tidak ada data timeslot untuk pilihan '{$workHours}'. Jalankan seeder atau tambahkan record di tabel timeslots terkait."
                    ),
                ])
                ->columns(1);

            return $components;
        }

        // 🔹 Header kolom (rapi dan terbaca)
        $components[] = Grid::make()
            ->schema([
                Placeholder::make('time')
                    ->content('')
                    ->extraAttributes(['style' => 'text-align:left;font-weight:700;font-size:13px;'])
                    ->columnSpan(2),

                Placeholder::make('target')
                    ->content('')
                    ->extraAttributes(['style' => 'text-align:center;font-weight:700;font-size:13px;'])
                    ->columnSpan(1),

                Placeholder::make('actual ')
                    ->label('Actual OK')
                    ->extraAttributes(['style' => 'text-align:center;font-weight:700;font-size:13px;'])
                    ->columnSpan(1),

                Placeholder::make('ng')
                    ->label('NG')
                    ->extraAttributes(['style' => 'text-align:center;font-weight:700;font-size:13px;'])
                    ->columnSpan(1),

                Placeholder::make('balance')
                    ->content('')
                    ->extraAttributes(['style' => 'text-align:center;font-weight:700;font-size:13px;'])
                    ->columnSpan(1),

                Placeholder::make('losstime')
                    ->content('*Menit')
                    ->extraAttributes(['style' => 'text-align:center;font-weight:700;font-size:13px;'])
                    ->columnSpan(1),

                Placeholder::make('remarks')
                ->content('')
                 ->extraAttributes(['style' => 'text-align:center;font-weight:700;font-size:13px;'])
                 ->columnSpan(3),
                  Placeholder::make('technician')
                ->content('')
                 ->extraAttributes(['style' => 'text-align:center;font-weight:700;font-size:13px; margin-right:10px;'])
                 ->columnSpan(2),
            ])
            ->columns([
                            'default' => 12, // HP
                            'xs' => 12,      // Tablet kecil
                            'sm' => 12,      // Tablet kecil
                            'lg' => 12,      // Desktop (TETAP)
                        ])
            ->extraAttributes([
                'class' => 'gap-2 pb-2 border-b-2 border-gray-200',
                'style' => 'min-width:900px; padding:0 16px;'
            ]);

        // Ukuran input: mobile kecil, tapi mulai dari 641px (sm) gunakan ukuran desktop (jejeran seperti monitor 20")
        // base: w-14 (mobile <640), sm: w-20 (>=640) — so 641-1007px will show like desktop
        $small = [
            // compact inputs for slot columns
            'class' => 'text-xs px-1 py-0.5 text-center',
            'style' => 'width:100%; max-width:65px; font-size:12px; padding:0; box-sizing:border-box;',
            'onwheel' => 'this.blur()',
        ];

        foreach ($timeSlots as $ts) {
            $slug = $ts->slug;

            // Baris utama per jam
            $components[] = Grid::make()
                ->schema([
                    Placeholder::make('time_' . $slug)->label('')->content($ts->label)
                    ->columnSpan(2),

                    TextInput::make('target_' . $slug)
                        ->label('')
                        ->numeric()
                        ->reactive()
                        ->dehydrated(true)
                        // allow manual edit even when value was auto-filled from `model`
                        ->readonly(false)
                        ->afterStateHydrated(function ($set, $get) use ($slug, $ts, $timeSlots) {
                            $model = $get('model');
                            if ($model) {
                                $target = \App\Models\TargetUph::whereRaw('LOWER(model_name)=?', [strtolower($model)])->first();
                                // If form was loaded from DB slots, do not auto-fill targets here
                                if (! ($get('slots_loaded_from_db') ?? false)) {
                                    if ($target) {
                                        $minutes = $ts->minutes ?? $ts->duration ?? 60;
                                        $perJam = (int) round(($target->target_per_hour / 60) * $minutes);
                                        $existing = (int) ($get('target_' . $slug) ?? 0);
                                        if ($existing <= 0) {
                                            $set('target_' . $slug, $perJam);
                                        }
                                    }
                                }
                            }
                            // Recalculate accumulative fields after hydration (when model sets targets)
                            $accTarget = $accActual = $accNg = $accLoss = 0;
                            $uph = (int) ($get('computed_uph') ?? 0);

                            foreach ($timeSlots as $ts2) {
                                $slug2 = $ts2->slug;
                                $t = (int) ($get('target_' . $slug2) ?? 0);
                                $act = (int) ($get('actual_ok_' . $slug2) ?? 0);
                                $ng = (int) ($get('ng_' . $slug2) ?? 0);

                                $computed_uph = (int) ($get('computed_uph') ?? 0);
                                $minutes = $ts2->minutes ?? $ts2->duration ?? 60;
                                if ($computed_uph > 0 && $act > 0) {
                                    $uph_slot = max(1, (int) round(($computed_uph / 60) * $minutes));
                                    $computedLoss = (int) round((1 - ($act / max(1, $uph_slot))) * $minutes);
                                    $computedLoss = max(0, $computedLoss);
                                } else {
                                    $computedLoss = 0;
                                }

                                $set('loss_time_' . $slug2, $computedLoss);
                                $loss = $computedLoss;

                                $balance = $act - $t;
                                if ($balance < 0) {
                                    $balance = 0;
                                }

                                $accTarget += $t;
                                $accActual += $act;
                                $accNg += $ng;
                                $accLoss += $loss;

                                $set('balance_' . $slug2, ($act - $t));
                                $set('target_acc_' . $slug2, $accTarget);
                                $set('actual_acc_' . $slug2, $accActual);
                                $set('ng_acc_' . $slug2, $accNg);
                                $set('loss_acc_' . $slug2, $accLoss);
                                $set('balance_acc_' . $slug2, ($accActual - $accTarget));
                            }

                            $set('target_total', $accTarget);
                            $set('actual_total', $accActual);
                            $set('ng_total', $accNg);
                            if ($accNg > 0) {
                                $items = $get('quality_information') ?? [];
                                if (count($items) === 0) {
                                    $set('quality_information', [[
                                        'process' => null,
                                        'ng_item' => null,
                                        'loc' => null,
                                        'qty' => null,
                                        'results_qc' => null,
                                        'sop_line' => null,
                                        'ipqc' => null,
                                        'remarks_qc' => null,
                                    ]]);
                                }
                            }
                            // keep Quality summary fields in sync immediately
                            $set('total_qty', (int) $accNg);
                            $set('loss_total', $accLoss);
                            $set('balance_total', ($accActual - $accTarget));
                            $set('output', $accActual);
                        })
                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($timeSlots) {
                            $accTarget = $accActual = $accNg = $accLoss = 0;

                            // Use cached UPH value to avoid DB queries while user types
                            $uph = (int) ($get('computed_uph') ?? 0);

                            foreach ($timeSlots as $ts) {
                                $slug2 = $ts->slug;
                                $target = (int) ($get('target_' . $slug2) ?? 0);
                                $act = (int) ($get('actual_ok_' . $slug2) ?? 0);
                                $ng = (int) ($get('ng_' . $slug2) ?? 0);

                                $computed_uph = (int) ($get('computed_uph') ?? 0);
                                $minutes = $ts->minutes ?? $ts->duration ?? 60;
                                if ($computed_uph > 0 && $act > 0) {
                                    $uph_slot = max(1, (int) round(($computed_uph / 60) * $minutes));
                                    $computedLoss = (int) round((1 - ($act / max(1, $uph_slot))) * $minutes);
                                    $computedLoss = max(0, $computedLoss);
                                } else {
                                    $computedLoss = 0;
                                }

                                $set('loss_time_' . $slug2, $computedLoss);
                                $loss = $computedLoss;

                                $balance = $act - $target;
                                if ($balance < 0) {
                                    $balance = 0;
                                }

                                // accumulate targets immediately when user inputs them
                                $accTarget += $target;
                                $accActual += $act;
                                $accNg += $ng;
                                $accLoss += $loss;

                                $set('balance_' . $slug2, ($act - $target));
                                $set('target_acc_' . $slug2, $accTarget);
                                $set('actual_acc_' . $slug2, $accActual);
                                $set('ng_acc_' . $slug2, $accNg);
                                $set('loss_acc_' . $slug2, $accLoss);
                                $set('balance_acc_' . $slug2, ($accActual - $accTarget));
                            }

                            $set('target_total', $accTarget);
                            $set('actual_total', $accActual);
                                $set('ng_total', $accNg);
                                if ($accNg > 0) {
                                    $items = $get('quality_information') ?? [];
                                    if (count($items) === 0) {
                                        $set('quality_information', [[
                                            'process' => null,
                                            'ng_item' => null,
                                            'loc' => null,
                                            'qty' => null,
                                            'results_qc' => null,
                                            'sop_line' => null,
                                            'ipqc' => null,
                                            'remarks_qc' => null,
                                        ]]);
                                    }
                                }
                            $set('total_qty', (int) $accNg);
                            $set('loss_total', $accLoss);
                            $set('balance_total', ($accActual - $accTarget));
                            $set('output', $accActual);

                            // If ng_total changed, keep total_qty in sync but DO NOT auto-prefill items.
                            $val = (int) $accNg;
                            $items = $get('quality_information') ?? [];
                            if ($val <= 0) {
                                // clear repeater when no NG
                                $set('quality_information', []);
                                $set('qty_ng', 0);
                                $set('qty_ok', 0);
                                $set('output_total_ok', (int) ($get('output') ?? 0) + 0 + (int) ($get('output_add') ?? 0));
                            } else {
                                // Recompute derived sums from existing items only
                                $ok = 0;
                                $ng = 0;
                                foreach ($items as $it) {
                                    $q = (int) ($it['qty'] ?? 0);
                                    $r = isset($it['results_qc']) ? strtoupper($it['results_qc']) : null;
                                    if ($q > 0 && $r === 'OK') {
                                        $ok += $q;
                                    } elseif ($q > 0 && $r === 'NG') {
                                        $ng += $q;
                                    }
                                }
                                $set('qty_ok', $ok);
                                // update total output whenever qty_ok changes programmatically
                                $output = (int) ($get('output') ?? 0);
                                $add = (int) ($get('output_add') ?? 0);
                                $set('output_total_ok', $output + $ok + $add);
                                $set('qty_ng', $ng);
                            }
                        })
                        ->placeholder('Target')
                        ->columnSpan(1),

                    TextInput::make('actual_ok_' . $slug)
                        ->label('')
                        ->numeric()
                        ->reactive()
                        ->minValue(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($timeSlots, $slug) {
                            // If user entered an actual value and target for this slot is empty,
                            // compute a reasonable target (from model's UPH or cached computed_uph)
                            if (! empty($state)) {
                                $currentTarget = (int) ($get('target_' . $slug) ?? 0);
                                if ($currentTarget <= 0) {
                                    $minutes = 60;
                                    foreach ($timeSlots as $tsx) {
                                        if (($tsx->slug ?? null) === $slug) {
                                            $minutes = $tsx->minutes ?? $tsx->duration ?? 60;
                                            break;
                                        }
                                    }

                                    $selectedModel = $get('model') ?? null;
                                    if (! empty($selectedModel)) {
                                        $targetModel = \App\Models\TargetUph::whereRaw('LOWER(model_name)=?', [strtolower($selectedModel)])->first();
                                        if ($targetModel) {
                                            $perJam = (int) round(($targetModel->target_per_hour / 60) * $minutes);
                                            $existing = (int) ($get('target_' . $slug) ?? 0);
                                            if ($existing <= 0) {
                                                $set('target_' . $slug, $perJam);
                                            }
                                        }
                                    } else {
                                        $computed_uph = (int) ($get('computed_uph') ?? 0);
                                        if ($computed_uph > 0) {
                                            $perJam = max(1, (int) round(($computed_uph / 60) * $minutes));
                                            $existing = (int) ($get('target_' . $slug) ?? 0);
                                            if ($existing <= 0) {
                                                $set('target_' . $slug, $perJam);
                                            }
                                        }
                                    }
                                }
                            }

                        // continue existing accumulation logic
                        foreach ($timeSlots as $ts) {
                            $slug2 = $ts->slug;
                            $target = (int) ($get('target_' . $slug2) ?? 0);
                            $act = (int) ($get('actual_ok_' . $slug2) ?? 0);
                            $ng = (int) ($get('ng_' . $slug2) ?? 0);
                            $accTarget = $accActual = $accNg = $accLoss = 0;

                            // Use cached UPH value to avoid DB queries while user types
                            $uph = (int) ($get('computed_uph') ?? 0);

                            }
                            foreach ($timeSlots as $ts) {
                                $slug2 = $ts->slug;
                                $target = (int) ($get('target_' . $slug2) ?? 0);
                                $act = (int) ($get('actual_ok_' . $slug2) ?? 0);
                                $ng = (int) ($get('ng_' . $slug2) ?? 0);

                                // Hitung loss time berdasarkan actual dan UPH disesuaikan per time slot
                                $computed_uph = (int) ($get('computed_uph') ?? 0);
                                $minutes = $ts->minutes ?? $ts->duration ?? 60;
                                if ($computed_uph > 0 && $act > 0) {
                                    $uph_slot = max(1, (int) round(($computed_uph / 60) * $minutes));
                                    $computedLoss = (int) round((1 - ($act / max(1, $uph_slot))) * $minutes);
                                    $computedLoss = max(0, $computedLoss);
                                } else {
                                    $computedLoss = 0;
                                }

                                $set('loss_time_' . $slug2, $computedLoss);

                                $loss = $computedLoss;

                                $balance = $act - $target;
                                if ($balance < 0) {
                                    $balance = 0;
                                }

                                if ($act > 0) {
                                    $accTarget += $target;
                                }
                                $accActual += $act;
                                $accNg += $ng;
                                $accLoss += $loss;

                                $set('balance_' . $slug2, ($act - $target));
                                $set('target_acc_' . $slug2, $accTarget);
                                $set('actual_acc_' . $slug2, $accActual);
                                $set('ng_acc_' . $slug2, $accNg);
                                $set('loss_acc_' . $slug2, $accLoss);
                                $set('balance_acc_' . $slug2, ($accActual - $accTarget));
                            }

                            $set('target_total', $accTarget);
                            $set('actual_total', $accActual);
                                $set('ng_total', $accNg);
                                if ($accNg > 0) {
                                    $items = $get('quality_information') ?? [];
                                    if (count($items) === 0) {
                                        $set('quality_information', [[
                                            'process' => null,
                                            'ng_item' => null,
                                            'loc' => null,
                                            'qty' => null,
                                            'results_qc' => null,
                                            'sop_line' => null,
                                            'ipqc' => null,
                                            'remarks_qc' => null,
                                        ]]);
                                    }
                                }
                            $set('total_qty', (int) $accNg);
                            $set('loss_total', $accLoss);
                            $set('balance_total', ($accActual - $accTarget));
                            $set('output', $accActual);
                        })
                        ->extraAttributes(array_merge($small, [
                            'onkeydown' => "if(event.key==='Enter'){this.blur();}",
                        ]))
                        ->placeholder('Actual')
                        ->columnSpan(1),

                    TextInput::make('ng_' . $slug)
                        ->label('')
                        ->numeric()
                        ->reactive()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($timeSlots) {
                            $accTarget = $accActual = $accNg = $accLoss = 0;

                            // Use cached UPH value to avoid DB queries while user types
                            $uph = (int) ($get('computed_uph') ?? 0);

                            foreach ($timeSlots as $ts) {
                                $slug2 = $ts->slug;
                                $target = (int) ($get('target_' . $slug2) ?? 0);
                                $act = (int) ($get('actual_ok_' . $slug2) ?? 0);
                                $ng = (int) ($get('ng_' . $slug2) ?? 0);

                                $computed_uph = (int) ($get('computed_uph') ?? 0);
                                $minutes = $ts->minutes ?? $ts->duration ?? 60;
                                if ($computed_uph > 0 && $act > 0) {
                                    $uph_slot = max(1, (int) round(($computed_uph / 60) * $minutes));
                                    $computedLoss = (int) round((1 - ($act / max(1, $uph_slot))) * $minutes);
                                    $computedLoss = max(0, $computedLoss);
                                } else {
                                    $computedLoss = 0;
                                }

                                $set('loss_time_' . $slug2, $computedLoss);
                                $loss = $computedLoss;

                                $balance = $act - $target;
                                if ($balance < 0) {
                                    $balance = 0;
                                }

                                if ($act > 0) {
                                    $accTarget += $target;
                                }
                                $accActual += $act;
                                $accNg += $ng;
                                $accLoss += $loss;

                                $set('balance_' . $slug2, ($act - $target));
                                $set('target_acc_' . $slug2, $accTarget);
                                $set('actual_acc_' . $slug2, $accActual);
                                $set('ng_acc_' . $slug2, $accNg);
                                $set('loss_acc_' . $slug2, $accLoss);
                                $set('balance_acc_' . $slug2, ($accActual - $accTarget));
                            }

                            $set('target_total', $accTarget);
                            $set('actual_total', $accActual);
                            $set('ng_total', $accNg);
                            if ($accNg > 0) {
                                $items = $get('quality_information') ?? [];
                                if (count($items) === 0) {
                                    $set('quality_information', [[
                                        'process' => null,
                                        'ng_item' => null,
                                        'loc' => null,
                                        'qty' => null,
                                        'results_qc' => null,
                                        'sop_line' => null,
                                        'ipqc' => null,
                                        'remarks_qc' => null,
                                    ]]);
                                }
                            }
                            $set('total_qty', (int) $accNg);
                            $set('loss_total', $accLoss);
                            $set('balance_total', ($accActual - $accTarget));
                            $set('output', $accActual);

                            // Do not auto-prefill repeater here. Keep existing repeater items.
                            // If accNg is zero, clear repeater and reset sums; otherwise just recompute sums.
                            $val = (int) $accNg;
                            $items = $get('quality_information') ?? [];
                            if ($val <= 0) {
                                $set('quality_information', []);
                                $set('qty_ng', 0);
                                $set('qty_ok', 0);
                                $set('output_total_ok', (int) ($get('output') ?? 0) + 0 + (int) ($get('output_add') ?? 0));
                                return;
                            }

                            $ok = 0;
                            $ng = 0;
                            foreach ($items as $it) {
                                $q = (int) ($it['qty'] ?? 0);
                                $r = isset($it['results_qc']) ? strtoupper($it['results_qc']) : null;
                                if ($q > 0 && $r === 'OK') {
                                    $ok += $q;
                                } elseif ($q > 0 && $r === 'NG') {
                                    $ng += $q;
                                }
                            }
                                $set('qty_ok', $ok);
                                $output = (int) ($get('output') ?? 0);
                                $add = (int) ($get('output_add') ?? 0);
                                $set('output_total_ok', $output + $ok + $add);
                                $set('qty_ng', $ng);
                        })
                        ->extraAttributes(array_merge($small, [
                            'onkeydown' => "if(event.key==='Enter'){this.blur();}",
                        ]))
                        ->placeholder('NG')
                        ->columnSpan(1),

                    TextInput::make('balance_' . $slug)
                        ->label('')
                        ->disabled()
                        ->extraAttributes($small)
                        ->placeholder('Balance')
                        ->columnSpan(1),

                    TextInput::make('loss_time_' . $slug)
                        ->label('')
                        ->numeric()
                        ->default(0)
                        ->reactive()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($timeSlots) {
                            $accTarget = $accActual = $accNg = $accLoss = 0;

                            // Use cached UPH value to avoid DB queries while user types
                            $uph = (int) ($get('computed_uph') ?? 0);

                            foreach ($timeSlots as $ts) {
                                $slug2 = $ts->slug;
                                $target = (int) ($get('target_' . $slug2) ?? 0);
                                $act = (int) ($get('actual_ok_' . $slug2) ?? 0);
                                $ng = (int) ($get('ng_' . $slug2) ?? 0);

                                $computed_uph = (int) ($get('computed_uph') ?? 0);
                                $minutes = $ts->minutes ?? $ts->duration ?? 60;
                                if ($computed_uph > 0 && $act > 0) {
                                    $uph_slot = max(1, (int) round(($computed_uph / 60) * $minutes));
                                    $computedLoss = (int) round((1 - ($act / max(1, $uph_slot))) * $minutes);
                                    $computedLoss = max(0, $computedLoss);
                                } else {
                                    $computedLoss = 0;
                                }

                                // jika user memasukkan nilai manual, kita tetap override agar konsisten
                                $set('loss_time_' . $slug2, $computedLoss);
                                $loss = $computedLoss;

                                $balance = $act - $target;
                                if ($balance < 0) {
                                    $balance = 0;
                                }

                                if ($act > 0) {
                                    $accTarget += $target;
                                }
                                $accActual += $act;
                                $accNg += $ng;
                                $accLoss += $loss;

                                $set('balance_' . $slug2, ($act - $target));
                                $set('target_acc_' . $slug2, $accTarget);
                                $set('actual_acc_' . $slug2, $accActual);
                                $set('ng_acc_' . $slug2, $accNg);
                                $set('loss_acc_' . $slug2, $accLoss);
                                $set('balance_acc_' . $slug2, ($accActual - $accTarget));
                            }

                            $set('target_total', $accTarget);
                            $set('actual_total', $accActual);
                            $set('ng_total', $accNg);
                            $set('total_qty', (int) $accNg);
                            $set('loss_total', $accLoss);
                            $set('balance_total', ($accActual - $accTarget));
                            $set('output', $accActual);
                        })
                        ->extraAttributes(array_merge($small, [
                            'onkeydown' => "if(event.key==='Enter'){this.blur();}",
                        ]))
                        ->placeholder('Loss')
                        ->columnSpan(1),

                    Forms\Components\Select::make('remarks_' . $slug)
                        ->label('')
                        ->options(fn () => array_merge([
                            'Other' => 'Other',
                        ], \Illuminate\Support\Facades\DB::table('list_ngs')->pluck('ng_name', 'ng_name')->toArray()))
                        ->reactive()
                        ->extraAttributes(array_merge($small, [
                            'style' => 'width:100%; max-width:220px; font-size:12px; padding:1px 2px; box-sizing:border-box;',
                            'onkeydown' => 'event.stopPropagation();', // agar enter tidak pindah ke baris lain
                        ]))
                        ->placeholder('Remarks')
                        ->columnSpan(3)
                        ->visible(fn (callable $get) => $get('remarks_' . $slug) !== 'Other')
                        ->afterStateUpdated(function (callable $set, callable $get, $state) use ($slug) {
                            // Pilih Other → set remarks_other kosong supaya input muncul
                            if ($state === 'Other') {
                                $set('remarks_other_' . $slug, '');
                            }

                            // Kosongkan state → reset ke null supaya select tetap muncul
                            if (empty($state)) {
                                $set('remarks_' . $slug, null);
                            }
                        }),

                    Forms\Components\TextInput::make('remarks_other_' . $slug)
                        ->label('')
                        ->reactive()
                        ->placeholder('Other...')
                        ->columnSpan(3)
                        ->extraAttributes(array_merge($small, [
                            'style' => 'width:100%; font-size:12px; padding:1px 2px; box-sizing:border-box;',
                            'onkeydown' => 'event.stopPropagation();',
                        ]))
                        ->visible(fn (callable $get) => $get('remarks_' . $slug) === 'Other')
                        ->afterStateUpdated(function (callable $set, callable $get, $state) use ($slug) {
                            // Kalau input dikosongkan → select muncul lagi
                            if (empty($state)) {
                                $set('remarks_' . $slug, null);
                            }
                        }),
                        Forms\Components\TextInput::make('name_techinician' . $slug)
                        ->label('')
                        ->reactive()
                        //->required()
                        ->placeholder('name_techinician')

                                ->afterStateUpdated(function ($state, callable $set, callable $get) use ($slug) {
                                            if (empty($state)) {
                                                return;
                                            }

                                            $record = \App\Models\TechnicianNameList::where('nik', $state)->first();
                                            if ($record) {
                                                // replace the input value for this slot with the name from the technician list
                                                $set('name_techinician' . $slug, $record->name);
                                            }
                                        })
                        ->columnSpan(2)
                        ->extraAttributes(array_merge($small, [
                            'style' => 'width:100%; font-size:12px; padding:0 0px; box-sizing:border-box;',
                            'onkeydown' => 'event.stopPropagation();',
                        ]))
                    ])



                ->columns([
                    'default' => 12, // HP
                    'xs' => 12, // HP
                    'sm' => 12,      // Tablet kecil
                    'lg' => 12,      // Desktop (TETAP)
                ])
                ->extraAttributes([
                    'class' => 'gap-1 pb-2 border-b-2 border-gray-200',
                    'style' => 'min-width:900px; padding:0 16px;'
                ]);

            // Baris akumulatif per jam
            $components[] = Grid::make()
                ->schema([
                    Placeholder::make('time_acc_' . $slug)
                        ->label('')
                        ->content('↳ Akumulatif')
                        ->columnSpan(2)
                        ->extraAttributes(['style' => 'vertical-align: middle; align-items:center;font-weight:600;']),

                    TextInput::make('target_acc_' . $slug)
                        ->label('')
                        ->disabled()
                        ->extraAttributes([
                            'class' => (($small['class'] ?? '') . ' xs:w-[260px]'),
                            'style' => 'width:100%; font-size:10px; padding:0; box-sizing:border-box; background-color:#DCDCDC !important;'
                        ])
                        ->placeholder('0'),

                    TextInput::make('actual_acc_' . $slug)
                        ->label('')
                        ->disabled()
                        ->extraAttributes([
                            'class' => (($small['class'] ?? '') . ' xs:w-[260px]'),
                            'style' => 'width:100%; font-size:10px; padding:0; box-sizing:border-box; background-color:#DCDCDC !important;'
                        ])
                        ->placeholder('0'),

                    TextInput::make('ng_acc_' . $slug)
                        ->label('')
                        ->disabled()
                        ->extraAttributes([
                            'class' => (($small['class'] ?? '') . ' xs:w-[260px]'),
                            'style' => 'width:100%; font-size:10px; padding:0; box-sizing:border-box; background-color:#DCDCDC !important;'
                        ])
                        ->placeholder('0'),

                    TextInput::make('balance_acc_' . $slug)
                        ->label('')
                        ->disabled()
                        ->extraAttributes([
                            'class' => (($small['class'] ?? '') . ' xs:w-[260px]'),
                            'style' => 'width:100%; font-size:10px; padding:0; box-sizing:border-box; background-color:#DCDCDC !important;'
                        ])
                        ->placeholder('0'),

                    TextInput::make('loss_acc_' . $slug)
                        ->label('')
                        ->disabled()
                        ->extraAttributes([
                            'class' => (($small['class'] ?? '') . ' xs:w-[260px]'),
                            'style' => 'width:100%; font-size:10px; padding:0; box-sizing:border-box; background-color:#DCDCDC !important;'
                        ])
                        ->placeholder('0'),
                ])
                ->columns([
                    'default' => 12, // HP
                    'xs' => 12, // HP
                    'sm' => 12,      // Tablet kecil
                    'lg' => 12,      // Desktop (TETAP)
                ])
                ->extraAttributes([
                    'class' => 'gap-1 pb-2 border-b-2 border-gray-200 width:150px',
                    'style' => 'min-width:900px; padding:0 16px;'
                ]);
        }

        // 🔻 Total bawah
        $components[] = Grid::make()
            ->schema([
                Placeholder::make('total_label')
                    ->label('')
                    ->content('TOTAL')
                    ->columnSpan(2)
                    ->extraAttributes(['style' => 'font-weight:bold;text-align:right;']),

                TextInput::make('target_total')
                    ->label('')
                    ->disabled()
                    ->dehydrated(true)
                    ->extraAttributes($small)
                    ->placeholder('0'),

                TextInput::make('actual_total')
                    ->label('')
                    ->readOnly()
                    ->reactive()
                    ->dehydrated(true)
                    ->extraAttributes($small)
                    ->placeholder('0'),

                TextInput::make('ng_total')
                    ->label('')
                    ->readOnly()
                    ->reactive()
                    ->dehydrated(true)
                    ->extraAttributes($small)
                    ->afterStateHydrated(function ($set, $get) {
                        // Ensure repeater exists on form load when ng_total already set
                        $val = (int) ($get('ng_total') ?? 0);
                        $set('total_qty', $val);

                        $items = $get('quality_information') ?? [];
                        if ($val > 0 && count($items) === 0) {
                            $set('quality_information', [[
                                'process' => null,
                                'ng_item' => null,
                                'loc' => null,
                                'qty' => null,
                                'results_qc' => null,
                                'sop_line' => null,
                                'ipqc' => null,
                                'remarks_qc' => null,
                            ]]);
                        }
                    })
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // when ng_total changes, keep total_qty in sync and auto-create one NG row
                        $val = (int) $state;
                        $set('total_qty', $val);

                        $items = $get('quality_information') ?? [];
                        if ($val <= 0) {
                            $set('quality_information', []);
                            $set('qty_ng', 0);
                            $set('qty_ok', 0);
                            $set('output_total_ok', (int) ($get('output') ?? 0) + 0 + (int) ($get('output_add') ?? 0));
                            return;
                        }

                        // If there is no existing repeater row, create a single NG row with qty = ng_total
                        if (count($items) === 0) {
                            $set('quality_information', [[
                                'process' => null,
                                'ng_item' => null,
                                'loc' => null,
                                'qty' => null,
                                'results_qc' => null,
                                'sop_line' => null,
                                'ipqc' => null,
                                'remarks_qc' => null,
                            ]]);
                            $items = $get('quality_information') ?? [];
                        }

                        // Recompute derived sums from existing repeater rows only
                        $ok = 0;
                        $ng = 0;
                        foreach ($items as $it) {
                            $q = (int) ($it['qty'] ?? 0);
                            $r = isset($it['results_qc']) ? strtoupper($it['results_qc']) : null;
                            if ($q > 0 && $r === 'OK') {
                                $ok += $q;
                            } elseif ($q > 0 && $r === 'NG') {
                                $ng += $q;
                            }
                        }
                        $set('qty_ok', $ok);
                        $output = (int) ($get('output') ?? 0);
                        $add = (int) ($get('output_add') ?? 0);
                        $set('output_total_ok', $output + $ok + $add);
                        $set('qty_ng', $ng);
                    })
                    ->placeholder('0'),

                TextInput::make('balance_total')
                    ->label('')
                    ->disabled()
                    ->dehydrated(true)
                    ->extraAttributes($small)
                    ->placeholder('0'),

                TextInput::make('loss_total')
                    ->label('')
                    ->disabled()
                    ->dehydrated(true)
                    ->extraAttributes($small)
                    ->placeholder('0'),

                //TextInput::make('remarks_total')
                //    ->label('')
                //   ->disabled()
                //    ->dehydrated(true)
                //    ->extraAttributes($small)
                //   ->placeholder('-'),
            ])
            ->columns([
                'default' => 12, // HP
                'xs' => 12, // HP
                'sm' => 12,      // Tablet kecil
                'lg' => 12      // Desktop (TETAP)
            ])
            ->extraAttributes([
                'class' => 'gap-1 pb-2 border-b-2 border-gray-200',
                'style' => 'min-width:900px; padding:0 16px;'
            ]);

        return $components;
    }

    /**
     * 🧾 Schema utama form
     */
    public static function form(Form $form): Form
    {
        $small = [
            'class' => 'small-input',
            'style' => 'font-size:0.75rem;padding:1px;width:70px;text-align:center;margin-right:1px;',
            'onwheel' => 'this.blur()',
        ];

        return $form->schema([
            Section::make('Production Details')
                ->extraAttributes(['style' => 'background-color:#a8c5e94f;'])
                ->schema([
                    Grid::make()
                        ->schema([
                            DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->native(false)
                            ->default(now())
                            ->displayFormat('d/m/y')  // tampilan: 02/01/25
                            ->disabled()
                            ->dehydrated(true),

                            Forms\Components\Select::make('select_shift')
                                ->label('Shift')
                                ->options(['1' => '1', '2' => '2', '3' => '3'])
                                ->required()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    // simpan nilai select_shift dan field shift
                                    $set('select_shift', $state);
                                    $set('shift', $state);

                                    // Jika work_hours belum terisi, tidak membangun timeslot
                                    $shift = $state ?? '1';
                                    $class5 = '\\App\\Models\\TimeSlotShift' . $shift . '_5h';
                                    $class7 = '\\App\\Models\\TimeSlotShift' . $shift . '_7h';

                                    $selectedWorkHours = $get('work_hours') ?? null;
                                    if (empty($selectedWorkHours)) {
                                        // belum ada work_hours → tidak membangun timeslot
                                        return;
                                    }

                                    $timeSlotForCheck = $selectedWorkHours === '5 Hours' ? $class5 : $class7;
                                    if (! class_exists($timeSlotForCheck)) {
                                        $alternate = $selectedWorkHours === '5 Hours' ? $class7 : $class5;
                                        if (class_exists($alternate)) {
                                            $selectedWorkHours = $selectedWorkHours === '5 Hours' ? '7 Hours' : '5 Hours';
                                        }
                                    }

                                    $timeSlotClass = $selectedWorkHours === '5 Hours' ? $class5 : $class7;
                                    if (! class_exists($timeSlotClass)) {
                                        $set('target_total', 0);
                                        $set('actual_total', 0);
                                        $set('ng_total', 0);
                                        $set('total_qty', 0);
                                        $set('loss_total', 0);
                                        $set('balance_total', 0);
                                        return;
                                    }

                                    $timeSlots = $timeSlotClass::orderBy('order')->get();

                                    $selectedModel = $get('model') ?? null;
                                    $targetModel = null;
                                    if (! empty($selectedModel)) {
                                        $targetModel = \App\Models\TargetUph::whereRaw('LOWER(model_name)=?', [strtolower($selectedModel)])->first();
                                        $set('computed_uph', $targetModel ? (int) $targetModel->target_per_hour : 0);
                                    } else {
                                        $set('computed_uph', 0);
                                    }

                                    $accTarget = $accActual = $accNg = $accLoss = 0;
                                    foreach ($timeSlots as $ts) {
                                        $slug = $ts->slug;
                                        $minutes = $ts->minutes ?? $ts->duration ?? 60;

                                        if ($targetModel) {
                                            $perJam = (int) round(($targetModel->target_per_hour / 60) * $minutes);
                                            $existing = (int) ($get('target_' . $slug) ?? 0);
                                            if ($existing <= 0) {
                                                $set('target_' . $slug, $perJam);
                                            }
                                        }

                                        $act = (int) ($get('actual_ok_' . $slug) ?? 0);
                                        $ng = (int) ($get('ng_' . $slug) ?? 0);

                                        $computed_uph = (int) ($get('computed_uph') ?? 0);
                                        if ($computed_uph > 0 && $act > 0) {
                                            $uph_slot = max(1, (int) round(($computed_uph / 60) * $minutes));
                                            $computedLoss = (int) round((1 - ($act / max(1, $uph_slot))) * $minutes);
                                            $computedLoss = max(0, $computedLoss);
                                        } else {
                                            $computedLoss = 0;
                                        }

                                        $set('loss_time_' . $slug, $computedLoss);

                                        $accTarget += (int) ($get('target_' . $slug) ?? 0);
                                        $accActual += $act;
                                        $accNg += $ng;
                                        $accLoss += $computedLoss;

                                        $set('balance_' . $slug, max(0, $act - (int) ($get('target_' . $slug) ?? 0)));
                                        $set('target_acc_' . $slug, $accTarget);
                                        $set('actual_acc_' . $slug, $accActual);
                                        $set('ng_acc_' . $slug, $accNg);
                                        $set('loss_acc_' . $slug, $accLoss);
                                        $set('balance_acc_' . $slug, ($accActual - $accTarget));
                                    }

                                    $set('target_total', $accTarget);
                                    $set('actual_total', $accActual);
                                    $set('ng_total', $accNg);
                                    $set('total_qty', (int) $accNg);
                                    $set('loss_total', $accLoss);
                                    $set('balance_total', ($accActual - $accTarget));
                                    $set('output', $accActual);
                                }),

                            Forms\Components\Select::make('select_group')
                                ->label('Group')
                                ->options(['A' => 'A', 'B' => 'B', 'C' => 'C'])
                                ->required()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('group', $state);
                                }),

                            Forms\Components\Select::make('work_hours')
                                ->label('Work Hours')
                                ->required()
                                ->reactive()
                                ->placeholder('Select an option')
                                ->options(['7 Hours' => '7 Hours', '5 Hours' => '5 Hours'])
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    // simpan pilihan
                                    $set('work_hours', $state);

                                    // Jika shift belum dipilih, tidak membangun timeslot
                                    $shift = $get('select_shift') ?? null;
                                    if (empty($shift)) {
                                        return;
                                    }

                                    $workHours = $state ?? null;
                                    $timeSlotClass = $workHours === '5 Hours'
                                        ? '\\App\\Models\\TimeSlotShift' . $shift . '_5h'
                                        : '\\App\\Models\\TimeSlotShift' . $shift . '_7h';

                                    if (! class_exists($timeSlotClass)) {
                                        // reset totals jika kelas timeslot tidak tersedia
                                        $set('target_total', 0);
                                        $set('actual_total', 0);
                                        $set('ng_total', 0);
                                        $set('total_qty', 0);
                                        $set('loss_total', 0);
                                        $set('balance_total', 0);
                                        return;
                                    }

                                    $timeSlots = $timeSlotClass::orderBy('order')->get();

                                    // jika ada model yang dipilih, isi ulang target per slot agar mengikuti timeslot baru
                                    $selectedModel = $get('model');
                                    if (! empty($selectedModel)) {
                                        $targetModel = \App\Models\TargetUph::whereRaw('LOWER(model_name)=?', [strtolower($selectedModel)])->first();
                                        if ($targetModel) {
                                            foreach ($timeSlots as $ts) {
                                                $minutes = $ts->minutes ?? $ts->duration ?? 60;
                                                $perJam = (int) round(($targetModel->target_per_hour / 60) * $minutes);
                                                $set('target_' . $ts->slug, $perJam);
                                            }
                                        }
                                    }

                                    $accTarget = $accActual = $accNg = $accLoss = 0;

                                    $modelForUph = $selectedModel ?? null;
                                    $uph = 0;
                                    if (! empty($modelForUph)) {
                                        $targetModelForUph = \App\Models\TargetUph::whereRaw('LOWER(model_name)=?', [strtolower($modelForUph)])->first();
                                        $uph = $targetModelForUph ? (int) $targetModelForUph->target_per_hour : 0;
                                        // cache for reactive updates
                                        $set('computed_uph', $uph);
                                    } else {
                                        $set('computed_uph', 0);
                                    }

                                    foreach ($timeSlots as $ts) {
                                        $slug = $ts->slug;
                                        $target = (int) ($get('target_' . $slug) ?? 0);
                                        $act = (int) ($get('actual_ok_' . $slug) ?? 0);
                                        $ng = (int) ($get('ng_' . $slug) ?? 0);

                                        $computed_uph = (int) ($uph ?? 0);
                                        $minutes = $ts->minutes ?? $ts->duration ?? 60;
                                        if ($computed_uph > 0 && $act > 0) {
                                            $uph_slot = max(1, (int) round(($computed_uph / 60) * $minutes));
                                            $computedLoss = (int) round((1 - ($act / max(1, $uph_slot))) * $minutes);
                                            $computedLoss = max(0, $computedLoss);
                                        } else {
                                            $computedLoss = (int) ($get('loss_time_' . $slug) ?? 0);
                                        }

                                        $set('loss_time_' . $slug, $computedLoss);
                                        $loss = $computedLoss;

                                        if ($act > 0) {
                                            $accTarget += $target;
                                        }
                                        $accActual += $act;
                                        $accNg += $ng;
                                        $accLoss += $loss;

                                        $set('target_acc_' . $slug, $accTarget);
                                        $set('actual_acc_' . $slug, $accActual);
                                        $set('ng_acc_' . $slug, $accNg);
                                        $set('loss_acc_' . $slug, $accLoss);
                                        $set('balance_' . $slug, ($act - $target));
                                        $set('balance_acc_' . $slug, ($accActual - $accTarget));
                                    }

                                    $set('target_total', $accTarget);
                                    $set('actual_total', $accActual);
                                    $set('ng_total', $accNg);
                                    $set('loss_total', $accLoss);
                                    $set('balance_total', ($accActual - $accTarget));
                                }),

                            TextInput::make('model')
                                ->required()
                                ->label('Model')
                                ->reactive()
                                ->columnSpan(3)
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    // Auto-fill model_output dengan nilai dari model
                                    $set('model_output', $state);

                                    $target = \App\Models\TargetUph::whereRaw('LOWER(model_name)=?', [strtolower($state)])->first();
                                    if (! $target) {
                                        // ensure cached UPH cleared when model not found
                                        $set('computed_uph', 0);
                                        return;
                                    }

                                    // cache UPH so subsequent field updates don't hit DB
                                    $set('computed_uph', (int) $target->target_per_hour);

                                    // pilih timeslot sesuai jam kerja yang sedang dipilih dan shift
                                    $workHours = $get('work_hours') ?? '7 Hours';
                                    $shift = $get('select_shift') ?? '1';
                                    $timeSlotClass = $workHours === '5 Hours'
                                        ? '\\App\\Models\\TimeSlotShift' . $shift . '_5h'
                                        : '\\App\\Models\\TimeSlotShift' . $shift . '_7h';

                                    if (class_exists($timeSlotClass)) {
                                        $timeSlots = $timeSlotClass::orderBy('order')->get();
                                    } else {
                                        $timeSlots = collect();
                                    }

                                    $accTarget = $accActual = $accNg = $accLoss = 0;

                                    foreach ($timeSlots as $ts) {
                                        $minutes = $ts->minutes ?? $ts->duration ?? 60;
                                        $perJam = (int) round(($target->target_per_hour / 60) * $minutes);
                                        $existing = (int) ($get('target_' . $ts->slug) ?? 0);
                                        if ($existing <= 0) {
                                            $set('target_' . $ts->slug, $perJam);
                                        }

                                        $act = (int) ($get('actual_ok_' . $ts->slug) ?? 0);
                                        $ng = (int) ($get('ng_' . $ts->slug) ?? 0);

                                        // Always accumulate target per slot when model fills targets
                                        $accTarget += $perJam;
                                        $set('target_acc_' . $ts->slug, $accTarget);

                                        $computed_uph = $target ? (int) $target->target_per_hour : 0;
                                        $minutes = $ts->minutes ?? $ts->duration ?? 60;
                                        if ($computed_uph > 0 && $act > 0) {
                                            $uph_slot = max(1, (int) round(($computed_uph / 60) * $minutes));
                                            $computedLoss = (int) round((1 - ($act / max(1, $uph_slot))) * $minutes);
                                            $computedLoss = max(0, $computedLoss);
                                        } else {
                                            $computedLoss = 0;
                                        }

                                        $set('loss_time_' . $ts->slug, $computedLoss);
                                        $loss = $computedLoss;

                                        $accActual += $act;
                                        $accNg += $ng;
                                        $accLoss += $loss;

                                        $set('actual_acc_' . $ts->slug, $accActual);
                                        $set('ng_acc_' . $ts->slug, $accNg);
                                        $set('loss_acc_' . $ts->slug, $accLoss);

                                        $set('balance_' . $ts->slug, max(0, $act - $perJam));
                                        $set('balance_acc_' . $ts->slug, ($accActual - $accTarget));
                                    }

                                    $set('target_total', $accTarget);
                                    $set('actual_total', $accActual);
                                    $set('ng_total', $accNg);
                                    $set('loss_total', $accLoss);
                                    $set('balance_total', ($accActual - $accTarget));
                                }),

                            TextInput::make('dj_number')
                                ->required()
                                ->label('DJ Number')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    // jika kosong, reset related fields
                                    if (empty($state)) {
                                        $set('dj_number', null);
                                        $set('customer_id', null);
                                        return;
                                    }

                                    // dukung format: "5001790525, 1123-06428" atau "5001790525,1123"
                                    $parts = preg_split('/\s*,\s*/', $state);

                                    // Ambil 10 digit pertama dari bagian pertama sebagai DJ Number
                                    $first = $parts[0] ?? '';
                                    $numbersOnly = preg_replace('/\D/', '', $first);
                                    $dj = substr($numbersOnly, 0, 10);
                                    $set('dj_number', $dj);

                                    // Jika ada bagian kedua setelah koma, ambil 4 digit pertama sebagai kode customer
                                    if (isset($parts[1]) && strlen(trim($parts[1])) > 0) {
                                        $secondDigits = preg_replace('/\D/', '', $parts[1]);
                                        $code = substr($secondDigits, 0, 4);
                                        if (! empty($code)) {
                                            $customer = \App\Models\Customer::where('code', $code)->first();
                                            if ($customer) {
                                                // isi nama customer dan id supaya Select menampilkan pilihan
                                                $set('customer_name', $customer->name);
                                                $set('customer_id', $customer->id);
                                            }
                                        }
                                    }
                                }),

                            Forms\Components\TextInput::make('customer_name')
                                ->required()
                                ->label('Customer')
                                ->columnSpan(2),

                            TextInput::make('line')->label('Line No')->required(),
                            Placeholder::make('Select Form')
                                         ->label('Form *')
                                         ->hidden()
                                         ->extraAttributes(['style' => 'display:flex; align-items:center;'])
                                         ->content(new \Illuminate\Support\HtmlString(<<<'HTML'
                                         <button type="button" id="openSelectionBtn" class="filament-button filament-button-size-md bg-blue-600 text-white" style="background-color:#2563eb;color:#ffffff;border:none;padding:6px 12px;border-radius:6px;" onclick="(function(){try{var sEl=document.querySelector('[name=\'select_shift\']');var gEl=document.querySelector('[name=\'select_group\']');var wEl=document.querySelector('[name=\'work_hours\']');var modalS=document.getElementById('modal_select_shift');var modalG=document.getElementById('modal_select_group');var modalW=document.getElementById('modal_select_workhours'); if(sEl&&modalS) modalS.value=sEl.value||''; if(gEl&&modalG) modalG.value=gEl.value||''; if(wEl&&modalW) modalW.value=wEl.value||''; var modal=document.getElementById('selectionModal'); if(modal) modal.style.display='flex';}catch(e){console.error(e);}})()">Select Form</button>

<div id="selectionModal" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center;">
    <div style="position:absolute; inset:0; background:rgba(0,0,0,0.5);"></div>
    <div style="position:relative; margin:auto; background:#fff; padding:16px; width:360px; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <h3 style="margin:0 0 8px 0;">Pilih Shift / Group / Work Hours</h3>
        <div style="font-size:14px; line-height:1.4; margin-bottom:12px;">
            <div style="margin-bottom:8px;">
                <label for="modal_select_shift" style="display:block;font-weight:600;">Shift</label>
                <select id="modal_select_shift" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
                    <option value="">-- Select Shift --</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>
            </div>
            <div style="margin-bottom:8px;">
                <label for="modal_select_group" style="display:block;font-weight:600;">Group</label>
                <select id="modal_select_group" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
                    <option value="">-- Select Group --</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                </select>
            </div>
            <div>
                <label for="modal_select_workhours" style="display:block;font-weight:600;">Work Hours</label>
                <select id="modal_select_workhours" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
                    <option value="">-- Select Work Hours --</option>
                    <option value="7 Hours">7 Hours</option>
                    <option value="5 Hours">5 Hours</option>
                </select>
            </div>
        </div>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button type="button" onclick="(function(){var m=document.getElementById('selectionModal'); if(m) m.style.display='none';})()" class="filament-button">Cancel</button>
            <button type="button" onclick="(function(){try{var modalS=document.getElementById('modal_select_shift');var modalG=document.getElementById('modal_select_group');var modalW=document.getElementById('modal_select_workhours');var sVal=modalS?modalS.value:'';var gVal=modalG?modalG.value:'';var wVal=modalW?modalW.value:'';function setFieldVal(name,val){if(typeof val==='undefined') val='';var el=document.querySelector('[name="'+name+'"]')||document.getElementById(name);if(!el){var list=Array.from(document.querySelectorAll('input,select,textarea'));el=list.find(function(x){var n=x.name||'';var id=x.id||'';var cls=x.className||'';var aria=x.getAttribute&&x.getAttribute('aria-label')||'';return n.indexOf(name)!==-1||id.indexOf(name)!==-1||cls.indexOf(name)!==-1||aria.indexOf(name)!==-1;});}if(!el) return false;try{if(el.tagName){var t=el.tagName.toLowerCase();if(t==='select'||t==='input'||t==='textarea'){el.value=val;el.dispatchEvent(new Event('input',{bubbles:true}));el.dispatchEvent(new Event('change',{bubbles:true}));return true;} }var inside=el.querySelector&&el.querySelector('input,select,textarea');if(inside){inside.value=val;inside.dispatchEvent(new Event('input',{bubbles:true}));inside.dispatchEvent(new Event('change',{bubbles:true}));return true;} }catch(e){}return false;}setFieldVal('select_shift',sVal);setFieldVal('select_group',gVal);setFieldVal('work_hours',wVal);var el=document.querySelector('[name="confirmed_selection"]')||document.getElementById('confirmed_selection');if(el){try{el.value='1';el.dispatchEvent(new Event('input',{bubbles:true}));}catch(e){el.setAttribute('value','1');}}var m=document.getElementById('selectionModal');if(m) m.style.display='none';}catch(e){console.error(e);} })()" class="filament-button filament-button-primary">Confirm</button>
        </div>
    </div>
</div>
HTML
                                                                ))
                                                                ->columnSpan(1),

                            // Hidden field to mark confirmation from popup
                            TextInput::make('confirmed_selection')
                                ->hidden(),

                            // Hidden flag: indicates form fields were loaded from DB slots
                            TextInput::make('slots_loaded_from_db')
                                ->hidden(),

                            // Hidden field untuk shift_group (disimpan dari select_shiftgroup)
                            TextInput::make('shift_group')->hidden(),
                            // Cache UPH per model agar tidak query DB berulang-ulang saat user mengetik actual/ng
                            TextInput::make('computed_uph')
                                ->hidden()
                                ->reactive()
                                ->dehydrated(false)
                                ->default(0),
                            // Ensure repeater is initialized on form load when ng_total >= 1
                            TextInput::make('ensure_quality_init')
                                ->hidden()
                                ->afterStateHydrated(function ($set, $get) {
                                    $ngTotal = (int) ($get('ng_total') ?? 0);
                                    $items = $get('quality_information') ?? [];
                                    if ($ngTotal >= 1 && count($items) === 0) {
                                        $set('quality_information', [[
                                            'process' => null,
                                            'ng_item' => null,
                                            'loc' => null,
                                            'qty' => null,
                                            'results_qc' => null,
                                            'sop_line' => null,
                                            'ipqc' => null,
                                            'remarks_qc' => null,
                                        ]]);
                                    }
                                }),
                        ])
                        ->columns([
                            'default' => 4, // HP
                            'xs' => 4,      // Tablet kecil
                            'sm' => 4,      // Tablet Advan
                            'lg' => 8,      // Desktop (TETAP)
                        ])
                        ->extraAttributes(['style' => 'gap:1px;margin-bottom:2px;']),
                ]),

            Section::make('Production Data')
                ->extraAttributes(['style' => 'background-color:#a8c5e94f; overflow-x:auto;'])
                ->schema(fn (callable $get) => self::getTimeListSchema($get('work_hours'), $get('select_shift')))
                ->reactive()
                ->columnSpanFull(),

            Section::make('Quality Information Sheet')
                ->extraAttributes([
                    'style' => 'background-color:#FFF3CD;',
                ])
                ->schema([
                    Repeater::make('quality_information')
                        ->default(fn (callable $get) => ((int) ($get('ng_total') ?? 0) > 0)
                            ? [[
                                'process' => null,
                                'ng_item' => null,
                                'loc' => null,
                                'qty' => null,
                                'results_qc' => null,
                                'sop_line' => null,
                                'ipqc' => null,
                                'remarks_qc' => null,
                            ]] : [])
                    ->label('')
                        ->schema([
                            Select::make('process')
                            ->label('Process')
                            ->required()
                            ->options(fn () => ProductionControlShift1::processList())
                            ->searchable()
                            ->columnSpan(2),

                            TextInput::make('ng_item')
                                ->label('NG Item')
                                 ->required()
                                ->columnSpan(1),

                            TextInput::make('loc')->label('Location')->columnSpan(1)->required(),



                            // Ensure qty input cannot make the sum exceed total_qty,
                            // and recompute qty_ok/qty_ng immediately when qty changes.
                            TextInput::make('qty')
                                ->label('QTY')
                                ->numeric()
                                ->minValue(0)
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $total = (int) ($get('total_qty') ?? 0);
                                    $items = $get('quality_information') ?? [];

                                    // compute current sum of all qtys
                                    $sum = 0;
                                    foreach ($items as $it) {
                                        $sum += (int) ($it['qty'] ?? 0);
                                    }

                                    if ($total > 0 && $sum > $total) {
                                        $excess = $sum - $total;
                                        $newVal = max(0, (int) $state - $excess);
                                        $set('qty', $newVal);
                                        $state = $newVal;
                                    }

                                    // recompute derived sums
                                    $ok = 0;
                                    $ng = 0;
                                    $items = $get('quality_information') ?? [];
                                    foreach ($items as $it) {
                                        $q = (int) ($it['qty'] ?? 0);
                                        $r = isset($it['results_qc']) ? strtoupper($it['results_qc']) : null;
                                        if ($q > 0 && $r === 'OK') {
                                            $ok += $q;
                                        } elseif ($q > 0 && $r === 'NG') {
                                            $ng += $q;
                                        }
                                    }
                                    $set('qty_ok', $ok);
                                    $output = (int) ($get('output') ?? 0);
                                    $add = (int) ($get('output_add') ?? 0);
                                    $set('output_total_ok', $output + $ok + $add);
                                    $set('qty_ng', $ng);
                                })
                                ->columnSpan(1),

                            Select::make('results_qc')
                                ->label('Results')
                                ->required()
                                ->reactive()

                                ->options(['OK' => 'OK', 'NG' => 'NG'])
                                ->reactive()
                                ->extraInputAttributes(fn (callable $get) => [
                                    'style' => ($get('results_qc') === 'OK')
                                        ? 'background-color:#16a34a;color:white;'
                                        : (($get('results_qc') === 'NG') ? 'background-color:#dc2626;color:white;' : ''),
                                ])
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    // recompute sums when result toggles
                                    $items = $get('quality_information') ?? [];
                                    $ok = 0;
                                    $ng = 0;
                                    foreach ($items as $it) {
                                        $q = (int) ($it['qty'] ?? 0);
                                        $r = isset($it['results_qc']) ? strtoupper($it['results_qc']) : null;
                                        if ($q > 0 && $r === 'OK') {
                                            $ok += $q;
                                        } elseif ($q > 0 && $r === 'NG') {
                                            $ng += $q;
                                        }
                                    }
                                    $set('qty_ok', $ok);
                                    $output = (int) ($get('output') ?? 0);
                                    $add = (int) ($get('output_add') ?? 0);
                                    $set('output_total_ok', $output + $ok + $add);
                                    $set('qty_ng', $ng);
                                })
                                ->columnSpan(1),

                            TextInput::make('sop_line')
                            ->label('SOP')
                            ->columnSpan(1)
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (empty($state)) {
                                        return;
                                    }

                                    $record = \App\Models\SopNameList::where('nik', $state)->first();
                                    if ($record) {
                                        // replace the input value with the name from the sop list
                                        $set('sop_line', $record->name);
                                    }
                                }),

                            TextInput::make('ipqc')
                            ->label('IPQC')
                            ->columnSpan(1)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (empty($state)) {
                                        return;
                                    }

                                    $record = \App\Models\IPQCNameList::where('nik', $state)->first();
                                    if ($record) {
                                        // replace the input value with the name from the ipqc list
                                        $set('ipqc', $record->name);
                                    }
                                })
                            ->required(),

                            TextInput::make('remarks_qc')->label('Remarks QC')->columnSpan(1)->required(),
                        ])
                        ->reactive()
                        ->afterStateHydrated(function ($set, $get) {
                            // If ng_total exists and there are no repeater rows, create a single NG row
                            $items = $get('quality_information') ?? [];
                            $ngTotal = (int) ($get('ng_total') ?? 0);
                            if ($ngTotal > 0 && count($items) === 0) {
                                $set('quality_information', [[
                                    'process' => null,
                                    'ng_item' => null,
                                    'loc' => null,
                                    'qty' => 0,
                                    'results_qc' => null,
                                    'sop_line' => null,
                                    'ipqc' => null,
                                    'remarks_qc' => null,
                                ]]);
                                // refresh items variable after creating the row
                                $items = $get('quality_information') ?? [];
                            }

                            // Recompute qty_ok and qty_ng from existing repeater rows only.
                            $ok = 0;
                            $ng = 0;
                            foreach ($items as $it) {
                                $qty = (int) ($it['qty'] ?? 0);
                                $res = isset($it['results_qc']) ? strtoupper($it['results_qc']) : null;
                                if ($qty > 0 && $res === 'OK') {
                                    $ok += $qty;
                                } elseif ($qty > 0 && $res === 'NG') {
                                    $ng += $qty;
                                }
                            }
                            $total = (int) ($get('total_qty') ?? 0);
                            if ($total > 0) {
                                // hanya pastikan qty_ng tidak melebihi total; jangan infer qty_ok dari selisih
                                $ng = min($ng, $total);
                            }
                            $set('qty_ok', $ok);
                            $output = (int) ($get('output') ?? 0);
                            $add = (int) ($get('output_add') ?? 0);
                            $set('output_total_ok', $output + $ok + $add);
                            $set('qty_ng', $ng);
                        })
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            // Recompute qty_ok and qty_ng from current repeater state only.
                            $items = $state ?? [];
                            $ok = 0;
                            $ng = 0;
                            foreach ($items as $it) {
                                $qty = (int) ($it['qty'] ?? 0);
                                $res = isset($it['results_qc']) ? strtoupper($it['results_qc']) : null;
                                if ($qty > 0 && $res === 'OK') {
                                    $ok += $qty;
                                } elseif ($qty > 0 && $res === 'NG') {
                                    $ng += $qty;
                                }
                            }
                            $total = (int) ($get('total_qty') ?? 0);
                            if ($total > 0) {
                                // hanya pastikan qty_ng tidak melebihi total; jangan infer qty_ok dari selisih
                                $ng = min($ng, $total);
                            }
                            $set('qty_ok', $ok);
                            $output = (int) ($get('output') ?? 0);
                            $add = (int) ($get('output_add') ?? 0);
                            $set('output_total_ok', $output + $ok + $add);
                            $set('qty_ng', $ng);
                        })
                        ->minItems(function (callable $get) {
                            return (((int) ($get('ng_total') ?? 0) > 0) || ((int) ($get('total_qty') ?? 0) > 0)) ? 1 : 0;
                        })
                        ->columns([
                            'default' => 3, // HP
                            'sm' => 4,      // Tablet Advan
                            'lg' => 8,      // Desktop (TETAP)
                        ])
                        ->maxItems(function (callable $get) {
                            $total = (int) ($get('total_qty') ?? 0);
                            $ngTotal = (int) ($get('ng_total') ?? 0);
                            // If ng_total is provided, allow up to that many repeater rows
                            if ($ngTotal > 0) {
                                return $ngTotal;
                            }
                            // fallback to previous behavior: limit by total_qty if available
                            if ($total > 0) {
                                return $total;
                            }
                            return null;
                        })
                        ->createItemButtonLabel('Add List NG')
                        ->reactive()
                        ->visible(fn (callable $get) => (((int) ($get('ng_total') ?? 0)) > 0)
                            || (((int) ($get('total_qty') ?? 0)) > 0)
                            || (count($get('quality_information') ?? []) > 0)
                        )
                        ->extraAttributes([
                            'class' => 'gap-1 pb-2 border-b-2 border-gray-200',
                        ]),
                            Grid::make()
                                ->schema([
                                    TextInput::make('qty_ok')
                                        ->label('QTY (OK)')
                                        ->numeric()
                                        ->readOnly()
                                        ->dehydrated(true)
                                        ->default(0)
                                         ->extraAttributes([
                                            'style' => 'text-align:center;background-color:#08cc66;',
                                        ])
                                        ->afterStateHydrated(function ($set, $get) {
                                            $val = (int) ($get('qty_ok') ?? 0);
                                            $output = (int) ($get('output') ?? 0);
                                            $add = (int) ($get('output_add') ?? 0);
                                            $set('output_total_ok', $output + $val + $add);
                                        })

                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $val = (int) ($state ?? 0);
                                            $output = (int) ($get('output') ?? 0);
                                            $add = (int) ($get('output_add') ?? 0);
                                            $set('output_total_ok', $output + $val + $add);
                                        })

                                        ->columnSpan(1),

                                    TextInput::make('qty_ng')
                                        ->label('QTY (NG)')
                                        ->numeric()
                                        ->readOnly()
                                        ->dehydrated(true)
                                        ->default(0)
                                        ->extraAttributes([
                                            'style' => 'text-align:center;background-color:#fc6c6c;',
                                        ])
                                        ->columnSpan(1),

                                    TextInput::make('total_qty')
                                        ->label('Total QTY')
                                        ->numeric()
                                        ->readOnly()
                                        ->reactive()
                                        ->dehydrated(true)
                                        ->afterStateHydrated(function ($set, $get) {
                                            // Prefer `ng_total` if present; otherwise use `actual_total`
                                            $ngTotal = (int) ($get('ng_total') ?? 0);
                                            $act = (int) ($get('actual_total') ?? 0);
                                            $val = $ngTotal > 0 ? $ngTotal : ($get('total_qty') ? (int) $get('total_qty') : ($act));
                                            $set('total_qty', $val);

                                            // Do NOT auto-prefill when ng_total present. If ng_total == 0, clear repeater and reset sums.
                                            if ($ngTotal === 0) {
                                                $set('quality_information', []);
                                                $set('qty_ok', 0);
                                                $set('output_total_ok', (int) ($get('output') ?? 0) + 0 + (int) ($get('output_add') ?? 0));
                                                $set('qty_ng', 0);
                                            }
                                        })
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $val = (int) ($state ?? 0);
                                            if ($val < 0) {
                                                return;
                                            }
                                            $ngTotal = (int) ($get('ng_total') ?? 0);
                                            // Do NOT auto-prefill. If ng_total is cleared, clear repeater.
                                            if ($ngTotal === 0) {
                                                $set('quality_information', []);
                                                $set('qty_ok', 0);
                                                $set('output_total_ok', (int) ($get('output') ?? 0) + 0 + (int) ($get('output_add') ?? 0));
                                                $set('qty_ng', 0);
                                                return;
                                            }

                                            // Otherwise keep existing items and recompute derived sums
                                            $items = $get('quality_information') ?? [];
                                            $ok = 0;
                                            $ng = 0;
                                            foreach ($items as $it) {
                                                $q = (int) ($it['qty'] ?? 0);
                                                $r = isset($it['results_qc']) ? strtoupper($it['results_qc']) : null;
                                                if ($r === 'OK') {
                                                    $ok += $q;
                                                } elseif ($r === 'NG') {
                                                    $ng += $q;
                                                }
                                            }
                                            $set('qty_ok', $ok);
                                            $output = (int) ($get('output') ?? 0);
                                            $add = (int) ($get('output_add') ?? 0);
                                            $set('output_total_ok', $output + $ok + $add);
                                            $set('qty_ng', $ng);
                                        })
                                        ->columnSpan(1),
                                ])
                                ->columns([
                                    'default' => 3,
                                    'sm' => 3,
                                    'lg' => 3,
                                ])
                                ->extraAttributes(['style' => 'gap:1px;']),

                ]),

            Section::make('Operator List')
                ->extraAttributes(['style' => 'background-color:#a8c5e94f;'])
                ->schema([
                    Repeater::make('operators')
                        ->label('')
                        ->schema([
                            Select::make('process')
                                ->label('Process')
                                ->options(fn () => ProductionControlShift1::processList())
                                ->searchable()
                                ->required(),

                            TextInput::make('name')
                                ->label('Name')
                                ->placeholder('Operator name')
                                ->required()


                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (empty($state)) {
                                        return;
                                    }

                                    $record = \App\Models\OperatosNameList::where('nik', $state)->first();
                                    if ($record) {
                                        // replace the input value with the name from the spv list
                                        $set('name', $record->name);
                                    }
                                }),


                        ])
                         ->columns([
                            'default' => 2, // HP
                            'sm' => 2,      // Tablet kecil
                            'lg' => 2,      // Desktop (TETAP)
                         ])
                        ->createItemButtonLabel('Add Operator')
                        ->minItems(1)
                        ->columnSpanFull(),
                ]),

            Section::make('Output After Change Model')
                ->extraAttributes(['style' => 'background-color:#a8c5e94f;'])
                ->schema([
                    Grid::make()
                        ->schema([
                            TextInput::make('model_output')
                               ->label('Model')
                               ->reactive()
                               ->columnSpan(3)
                               ->hidden()
                               ->afterStateHydrated(function ($set, $get) {
                                  // Saat load, isi dari field model
                                   $model = $get('model');
                                   if ($model) {
                                       $set('model_output', $model);
                                   }
                               })
                               ->dehydrated(true),

                            TextInput::make('start_time')->label('Start')->required()->type('time'),
                            TextInput::make('end_time')->label('End')->required()->type('time'),
                            TextInput::make('output')
                                ->label('Output')
                                ->numeric()
                                ->reactive()
                                ->readOnly()
                                ->afterStateHydrated(function ($set, $get) {
                                    // Auto-fill output = actual_total - output_add saat load
                                    $actualTotal = (int) ($get('actual_total') ?? 0);
                                    $add = (int) ($get('output_add') ?? 0);
                                    $set('output', $actualTotal - $add);
                                    // output_total_ok = output + qty_ok + output_add
                                    $output = (int) ($get('output') ?? ($actualTotal - $add));
                                    $qtyOk = (int) ($get('qty_ok') ?? 0);
                                    $add2 = (int) ($get('output_add') ?? 0);
                                    $set('output_total_ok', $output + $qtyOk + $add2);
                                })
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $o = (int) ($state ?? 0);
                                    $qtyOk = (int) ($get('qty_ok') ?? 0);
                                    $add = (int) ($get('output_add') ?? 0);
                                    $set('output_total_ok', $o + $qtyOk + $add);
                                }),
                            // TextInput::make('start_time_add')->label('Start')->type('time'),
                            // TextInput::make('end_time_add')->label('End')->type('time'),
                              TextInput::make('output_add')
                               ->label('Output')
                               ->numeric()
                                ->reactive()
                                ->hidden()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $add = (int) ($state ?? 0);
                                    $actual = (int) ($get('actual_total') ?? 0);
                                    $newOutput = $actual - $add;
                                    $set('output', $newOutput);
                                    $qtyOk = (int) ($get('qty_ok') ?? 0);
                                    $set('output_total_ok', $newOutput + $qtyOk + $add);
                                }),
                            TextInput::make('remark_output')->label('Remark')->columnSpan(2)->hidden(),

                            TextInput::make('output_total_ok')
                                ->label('Total Output OK')
                                ->numeric()
                                ->reactive()
                                ->readOnly()
                                ->afterStateHydrated(function ($set, $get) {
                                    $output = (int) ($get('output') ?? 0);
                                    $qtyOk = (int) ($get('qty_ok') ?? 0);
                                    $add = (int) ($get('output_add') ?? 0);
                                    $set('output_total_ok', $output + $qtyOk + $add);
                                }),

                        ])
                        ->columns([
                            'default' => 3, // HP
                            'sm' => 4,      // Tablet advan
                            'xs' => 4,      // Tablet kecil
                            'lg' => 4,      // Desktop (TETAP)
                        ])
                        ->extraAttributes(['style' => 'gap:1px;margin-bottom:2px;']),
                ]),

            Section::make('Approval')
                ->extraAttributes(['style' => 'background-color:#a8c5e94f;'])
                ->schema([
                    Grid::make()
                        ->schema([
                            TextInput::make('issued_sop')
                                ->label('SOP')
                                ->placeholder('Name SOP')
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (empty($state)) {
                                        return;
                                    }

                                    $record = \App\Models\SopNameList::where('nik', $state)->first();
                                    if ($record) {
                                        // replace the input value with the name from the sop list
                                        $set('issued_sop', $record->name);
                                    }
                                }),
                            TextInput::make('checked_leader')
                            ->label('Leader')
                            ->placeholder('Name Leader')
                            ->required()
                            ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (empty($state)) {
                                        return;
                                    }

                                    $record = \App\Models\LeaderNameList::where('nik', $state)->first();
                                    if ($record) {
                                        // replace the input value with the name from the leader list
                                        $set('checked_leader', $record->name);
                                    }
                                }),
                            TextInput::make('approved_spv')
                            ->label('SPV')
                            ->placeholder('Name SPV')
                            ->required()
                            ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (empty($state)) {
                                        return;
                                    }

                                    $record = \App\Models\SpvNameList::where('nik', $state)->first();
                                    if ($record) {
                                        // replace the input value with the name from the spv list
                                        $set('approved_spv', $record->name);
                                    }
                                }),
                        ])
                        ->columns([
                            'default' => 3, // HP
                            'sm' => 3,      // Tablet kecil
                            'lg' => 3,      // Desktop (TETAP)
                        ])
                        ->extraAttributes(['style' => 'gap:2px;margin-bottom:2px;']),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->sortable()->searchable()->date('d M Y'),
                Tables\Columns\TextColumn::make('line')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('model')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('select_shift')->label('Shift')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('select_group')->label('Group')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $state ?: ($record->customer->name ?? '-')),
                Tables\Columns\BadgeColumn::make('downloaded_at')
                    ->label('Downloaded')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->colors([
                        'success' => fn ($state) => ! empty($state),
                        'secondary' => fn ($state) => empty($state),
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('dj_number')->sortable()->searchable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('From'),
                        Forms\Components\DatePicker::make('created_until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'] ?? null, fn ($q, $v) => $q->whereDate('date', '>=', $v))
                            ->when($data['created_until'] ?? null, fn ($q, $v) => $q->whereDate('date', '<=', $v));
                    }),

                Tables\Filters\SelectFilter::make('customer_name')
                    ->options(fn () => \App\Models\Customer::orderBy('name')->pluck('name', 'name')->toArray()),

                Tables\Filters\SelectFilter::make('model')
                    ->options(fn () => ProductionControlShift1::query()->distinct()->orderBy('model')->pluck('model', 'model')->toArray()),

                Tables\Filters\SelectFilter::make('select_shift')
                    ->options(['1' => '1', '2' => '2', '3' => '3']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => route('production_control.download', ['production_control' => $record]))
                    ->openUrlInNewTab()
                    ->tooltip('Download report as PDF'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('download_selected')
                    ->label('Download All')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $ids = $records->pluck('id')->implode(',');
                        return redirect()->route('production_control.download_many', ['ids' => $ids]);
                    }),
            ]);
    }
 protected function getRedirectUrl(): string
    {
        // Redirect setelah berhasil simpan ke halaman List
        return $this->getResource()::getUrl('index');
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionControlShift1s::route('/'),
            'create' => Pages\CreateProductionControlShift1::route('/create'),
            'edit' => Pages\EditProductionControlShift1::route('/{record}/edit'),
        ];
    }
}
