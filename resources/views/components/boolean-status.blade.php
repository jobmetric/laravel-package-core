<!--begin::Status-->
<div class="card card-flush py-4">
    <div class="card-header">
        <div class="card-title">
            <span class="fs-5 fw-bold">{{ trans('package-core::base.components.boolean_status.label') }}</span>
        </div>
    </div>
    <div class="card-body pt-0">
        <select name="status" class="form-select" data-control="select2" data-hide-search="true">
            <option value="1" @if((bool)$value === true) selected @endif>{{ trans('package-core::base.components.boolean_status.enable') }}</option>
            <option value="0" @if((bool)$value === false) selected @endif>{{ trans('package-core::base.components.boolean_status.disable') }}</option>
        </select>
        @error('status')
        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
        @enderror
        <div class="mt-5 text-gray-600 fs-7">{{ trans('package-core::base.components.boolean_status.description') }}</div>
    </div>
</div>
<!--end::Status-->
