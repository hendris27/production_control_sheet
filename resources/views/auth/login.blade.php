@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Sign in</div>

                    <div class="card-body">
                        <form
                            method="POST"
                            action="{{ route('login') }}"
                        >
                            @csrf

                            <div class="form-group row">
                                <label
                                    for="nik"
                                    class="col-md-4 col-form-label text-md-right"
                                >NIK</label>

                                <div class="col-md-6">
                                    <input
                                        id="nik"
                                        type="text"
                                        class="form-control @error('nik') is-invalid @enderror"
                                        name="nik"
                                        value="{{ old('nik') }}"
                                        required
                                        autofocus
                                    >

                                    @error('nik')
                                        <span
                                            class="invalid-feedback"
                                            role="alert"
                                        >
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mt-3">
                                <label
                                    for="password"
                                    class="col-md-4 col-form-label text-md-right"
                                >Password</label>

                                <div class="col-md-6">
                                    <input
                                        id="password"
                                        type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        name="password"
                                        required
                                    >

                                    @error('password')
                                        <span
                                            class="invalid-feedback"
                                            role="alert"
                                        >
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mt-3">
                                <div class="col-md-6 offset-md-4">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="remember"
                                            id="remember"
                                            {{ old('remember') ? 'checked' : '' }}
                                        >

                                        <label
                                            class="form-check-label"
                                            for="remember"
                                        >
                                            Remember Me
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mb-0 mt-4">
                                <div class="col-md-8 offset-md-4">
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >
                                        Sign in
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
