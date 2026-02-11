<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <title>Production Control Sheet</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 2mm;
            /* equal left/right/top/bottom margins */
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 0;
            /* handled by @page */
            padding: 0;
            box-sizing: border-box;
            /* allow content to flow within page margins */
        }

        .header {
            margin-bottom: 8px
        }

        .meta {
            width: 100%;
            margin-bottom: 5px
        }

        .meta td {
            padding: 4px 6px
        }

        /* aligned label / colon / value for meta section
           - label: left-aligned text
           - colon: narrow column centered (keeps ':' vertically aligned)
           - value: left-aligned
        */
        .meta-label {
            text-align: left;
            width: 15px;
            vertical-align: middle;
            padding-right: 0px;
        }

        .meta-colon {
            text-align: center;
            vertical-align: middle;
            padding: 0px;
        }

        .meta-value {
            text-align: left;
            width: 45%;
            vertical-align: middle;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px
        }

        table.grid th,
        table.grid td {
            border: 1px solid #aaa;
            padding: 6px;
            vertical-align: middle
        }

        table.grid th {
            background: #f2f2f2;
            font-weight: 700;
        }

        .small {
            font-size: 10px;
            width: 100%;
        }

        .section-title {
            padding: 4px;
            font-weight: 700;
            margin-top: 5px;
            text-align: center;
            font-size: 14px;
            text-decoration: underline;
        }

        .center {
            text-align: center
        }
    </style>
</head>

<body>
    <div class="header">
        <div style="font-size:14px;font-weight:700;text-align:center">Production Control Sheet</div>
    </div>

    <table class="meta">
        <tr>
            <td class="meta-label"><strong>Model</strong></td>
            <td class="meta-colon">:</td>
            <td class="meta-value">{{ $record->model }}</td>

            <td class="meta-label"><strong>Customer</strong></td>
            <td class="meta-colon">:</td>
            <td class="meta-value">{{ $record->customer_name }}</td>

            <td class="meta-label"><strong>DJ No</strong></td>
            <td class="meta-colon">:</td>
            <td class="meta-value">{{ $record->dj_number }}</td>

        </tr>
        <tr>
            <td class="meta-label"><strong>Date</strong></td>
            <td class="meta-colon">:</td>
            <td class="meta-value">{{ optional($record->date)->format('d/m/Y') ?? $record->date }}</td>

            <td class="meta-label"><strong>Line</strong></td>
            <td class="meta-colon">:</td>
            <td class="meta-value">{{ $record->line }}</td>

            <td class="meta-label"><strong>Shift/Group</strong></td>
            <td class="meta-colon">:</td>
            <td class="meta-value">{{ $record->select_shift }} / {{ $record->select_group }}</td>
        </tr>
    </table>

    <!-- <div class="section-title">Production Data (per timeslot)</div> -->

    @php
        $workHours = $record->work_hours ?? '7 Hours';
        // determine shift from the persisted select or legacy shift field
        $shift = $record->select_shift ?? ($record->shift ?? '1');

        // prefer the class matching the selected shift; fall back to shift 1 if missing
        $timeSlotClass =
            $workHours === '5 Hours'
                ? "\\App\\Models\\TimeSlotShift{$shift}_5h"
                : "\\App\\Models\\TimeSlotShift{$shift}_7h";

        if (!class_exists($timeSlotClass)) {
            $timeSlotClass =
                $workHours === '5 Hours' ? '\\App\\Models\\TimeSlotShift1_5h' : '\\App\\Models\\TimeSlotShift1_7h';
        }

        $timeSlots = class_exists($timeSlotClass) ? $timeSlotClass::orderBy('order')->get() : collect();
    @endphp

    <table class="small grid">
        <thead>
            <tr>
                <th style="min-width:130px">Time</th>
                <th>Target</th>
                <th>Actual OK</th>
                <th>NG</th>
                <th>Balance</th>
                <th>Loss</th>
                <th style="min-width:220px">Remarks</th>
                <th style="min-width:100px">Technician</th>
            </tr>
        </thead>
        <tbody>
            @php
                $accTarget = $accActual = $accNg = $accLoss = $accBalance = 0;
            @endphp
            @foreach ($timeSlots as $ts)
                @php
                    $slug = $ts->slug;

                    // Prefer slots JSON stored on the record (saved from form). If not present, fall back.
                    $slot =
                        is_array($record->slots ?? null) && array_key_exists($slug, $record->slots)
                            ? $record->slots[$slug]
                            : null;

                    // Target: prefer saved slot target, else compute from TargetUph if model available, else empty
                    if (!empty($slot) && array_key_exists('target', $slot)) {
                        $target = (int) $slot['target'];
                    } else {
                        $target = (int) ($record->{'target_' . $slug} ?? 0);
                        if (empty($target) && !empty($record->model)) {
                            $t = \App\Models\TargetUph::whereRaw('LOWER(model_name)=?', [
                                strtolower($record->model),
                            ])->first();
                            if ($t) {
                                $minutes = $ts->minutes ?? ($ts->duration ?? 60);
                                $target = (int) round(($t->target_per_hour / 60) * $minutes);
                            }
                        }
                    }

                    $actual = (int) ($slot['actual_ok'] ?? ($record->{'actual_ok_' . $slug} ?? 0));
                    $ng = (int) ($slot['ng'] ?? ($record->{'ng_' . $slug} ?? 0));
                    $loss = (int) ($slot['loss_time'] ?? ($record->{'loss_time_' . $slug} ?? 0));
                    // Balance = actual - target (per new rule)
                    if (!empty($slot) && array_key_exists('balance', $slot)) {
                        $balance = (int) $slot['balance'];
                    } else {
                        $balance = $actual - $target;
                    }

                    $remarks =
                        $slot['remarks'] ??
                        ($record->{'remarks_' . $slug} ?? ($record->{'remarks_other_' . $slug} ?? ''));
                    $tech = $slot['technician'] ?? ($record->{'name_techinician' . $slug} ?? '');

                    // accumulate for totals and accumulative row
                    $accTarget += $target;
                    $accActual += $actual;
                    $accNg += $ng;
                    $accLoss += $loss;
                    $accBalance += $balance;
                @endphp

                <tr>
                    <td>{{ $ts->label }}</td>
                    <td class="center">{{ $target }}</td>
                    <td class="center">{{ $actual }}</td>
                    <td class="center">{{ $ng }}</td>
                    <td class="center">{{ $balance }}</td>
                    <td class="center">{{ $loss }}</td>
                    <td>{{ $remarks }}</td>
                    <td>{{ $tech }}</td>
                </tr>

                <tr style="background:#fafafa;font-weight:600">
                    <td> Akumulatif</td>
                    <td class="center">{{ $accTarget }}</td>
                    <td class="center">{{ $accActual }}</td>
                    <td class="center">{{ $accNg }}</td>
                    <td class="center">{{ $accBalance }}</td>
                    <td class="center">{{ $accLoss }}</td>
                    <td colspan="2"></td>
                </tr>
            @endforeach

            @if ($timeSlots->isEmpty())
                @for ($i = 0; $i < 8; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            @endif
        </tbody>
        <tfoot>
            <tr>
                <th>TOTAL</th>
                <th class="center">{{ $record->target_total ?? '' }}</th>
                <th class="center">{{ $record->actual_total ?? '' }}</th>
                <th class="center">{{ $record->ng_total ?? '' }}</th>
                <th class="center">{{ $record->balance_total ?? '' }}</th>
                <th class="center">{{ $record->loss_total ?? '' }}</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">Quality Information Sheet</div>

    <table class="small grid">
        <thead>
            <tr class="center">
                <th style="width:20%">Process</th>
                <th>NG Item</th>
                <th>Location</th>
                <th style="min-width:20px">QTY</th>
                <th>Result</th>
                <th>SOP/LDR</th>
                <th>IPQC</th>
                <th>Remarks QC</th>
            </tr>
        </thead>
        <tbody>
            @php $qiList = $record->quality_information ?? []; @endphp
            @if (!empty($qiList) && is_iterable($qiList))
                @foreach ($qiList as $qi)
                    @php $res = is_array($qi) ? ($qi['results_qc'] ?? '') : ($qi->results_qc ?? ''); @endphp
                    <tr class="center">
                        <td>{{ is_array($qi) ? $qi['process'] ?? '' : $qi->process ?? '' }}</td>
                        <td>{{ is_array($qi) ? $qi['ng_item'] ?? '' : $qi->ng_item ?? '' }}</td>
                        <td>{{ is_array($qi) ? $qi['loc'] ?? '' : $qi->loc ?? '' }}</td>
                        <td style="text-align:center">{{ is_array($qi) ? $qi['qty'] ?? '' : $qi->qty ?? '' }}</td>
                        <td class="center">
                            @if (strtoupper($res) === 'OK')
                                <span style="color:#0a0;font-weight:700">{{ $res }}</span>
                            @elseif(strtoupper($res) === 'NG')
                                <span style="color:#c00;font-weight:700">{{ $res }}</span>
                            @else
                                {{ $res }}
                            @endif
                        </td>
                        <td>{{ is_array($qi) ? ($qi['sop_line'] ?? '' ?: $qi['sop_adr'] ?? '') : $qi->sop_line ?? ($qi->sop_adr ?? '') }}
                        </td>
                        <td>{{ is_array($qi) ? $qi['ipqc'] ?? '' : $qi->ipqc ?? '' }}</td>
                        <td>{{ is_array($qi) ? $qi['remarks_qc'] ?? '' : $qi->remarks_qc ?? '' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td
                        colspan="8"
                        style="text-align:center"
                    >No quality information</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="section-title">Operator List</div>
    <table class="small grid">
        <thead>
            <tr class="center">
                <th style="width:30%">Process</th>
                <th style="width:20%">Name</th>
                <th style="width:30%">Process</th>
                <th style="width:20%">Name</th>
            </tr>
        </thead>
        <tbody>
            @php
                $ops = $record->operators ?? [];
                if (is_iterable($ops)) {
                    $ops = is_array($ops) ? $ops : iterator_to_array($ops);
                } else {
                    $ops = [];
                }
                $chunks = array_chunk($ops, 2);
            @endphp

            @if (!empty($chunks))
                @foreach ($chunks as $pair)
                    <tr class="center">
                        @foreach ($pair as $op)
                            @php
                                $procVal = is_array($op) ? $op['process'] ?? null : $op->process ?? null;
                                $processName = '';
                                if (!empty($procVal)) {
                                    if (is_numeric($procVal)) {
                                        $procModel = \App\Models\Process::find($procVal);
                                        $processName = $procModel->nama_proses ?? $procVal;
                                    } else {
                                        $processName = $procVal;
                                    }
                                }
                                $personName = is_array($op) ? $op['name'] ?? '' : $op->name ?? '';
                            @endphp
                            <td>{{ $processName }}</td>
                            <td>{{ $personName }}</td>
                        @endforeach

                        @if (count($pair) === 1)
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4">&nbsp;</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="section-title">Output After Change Model</div>
    <table class="small grid">
        <tbody>
            @php
                // Format times to remove seconds (HH:MM)
                $formatTime = function ($value) {
                    if (empty($value)) {
                        return '';
                    }

                    try {
                        return \Carbon\Carbon::parse($value)->format('H:i');
                    } catch (\Throwable $e) {
                        // fallback: strip trailing :ss if present
                        return preg_replace('/:\\d{2}$/', '', (string) $value);
                    }
                };

                $start = $formatTime($record->start_time ?? '');
                $end = $formatTime($record->end_time ?? '');

                // Calculate output according to form logic:
                // output = actual_total - output_add
                // output_total_ok = output + qty_ok
                $actualTotal = (int) ($record->actual_total ?? 0);
                $outputAdd = (int) ($record->output_add ?? 0);
                $qtyOk = (int) ($record->qty_ok ?? 0);
                $output = $actualTotal - $outputAdd;
                $outputTotalOk = $output + $qtyOk;
            @endphp

            <tr
                style="background:#fafafa; font-weight:700"
                class="center"
            >
                <td>Model</td>
                <td>Start</td>
                <td>End</td>
                <td>Output</td>
                <td style="background:#0a0; color:#fff;">Total Output OK</td>
                <td>Remarks</td>
            </tr>
            <tr class="center">
                <td>{{ $record->model }}</td>
                <td>{{ $start }}</td>
                <td>{{ $end }}</td>
                <td class="center">{{ $output }}</td>
                <td
                    class="center"
                    style="color:#0a0;font-weight:700"
                >{{ $outputTotalOk }}</td>
                <td>{{ $record->remark_output }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Approval</div>
    <table class="small grid">
        <tbody>
            <tr>
                <td
                    style="font-weight:700; width:20%"
                    class="center"
                >SOP</td>
                <td
                    style="width:30%"
                    class="center"
                >{{ $record->issued_sop }}</td>
                <td
                    style="font-weight:700; width:20%"
                    class="center"
                >Leader</td>
                <td
                    style="width:30%"
                    class="center"
                >{{ $record->checked_leader }}</td>
                <td
                    style="font-weight:700; width:20%"
                    class="center"
                >SPV</td>
                <td
                    style="width:30%"
                    class="center"
                >{{ $record->approved_spv }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:8px;font-size:10px;color:#666">QR-PROD-11-K008(Rev.09)</div>
</body>

</html>
