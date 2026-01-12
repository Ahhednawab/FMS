@extends('layouts.admin')

@section('title', 'Create Station')

@section('content')
    <div class="page-header page-header-light">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex">
                <h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">Create Station</span></h4>
                <a href="#" class="header-elements-toggle text-body d-lg-none"><i class="icon-more"></i></a>
            </div>
            <div class="header-elements d-none">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('stations.index') }}" class="btn btn-primary">
                        <span>View Station <i class="icon-list ml-2"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('stations.store') }}" enctype="multipart/form-data">
                            @csrf
                            @if (isset($draftId))
                                <input type="hidden" name="draft_id" value="{{ $draftId }}">
                            @endif

                            <div class="row">
                                <!-- Serial No -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Serial No</strong>
                                        <input type="text" name="serial_no" class="form-control"
                                            value="{{ $serial_no }}" readonly>
                                    </div>
                                </div>

                                <!-- Area -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <strong>Area</strong>
                                        <input type="text" name="area" class="form-control"
                                            value="{{ $draftData['area'] ?? old('area') }}">
                                        @if ($errors->has('area'))
                                            <label class="text-danger">{{ $errors->first('area') }}</label>
                                        @endif
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-md-6">
                                    <label for=""></label>
                                    <div class="text-right">
                                        <button type="submit" name="save_draft" value="1" class="btn btn-secondary">
                                            <i class="icon-save"></i> Draft
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="icon-check"></i> Save
                                        </button>
                                        <a href="{{ route('stations.index') }}" class="btn btn-warning">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
