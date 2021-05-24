<div class="form-group {!! !$errors->has($label) ?: 'has-error' !!}">

    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>

    <div class="col-sm-6">

        @include('admin::form.error')
        <input type="file" class="form-control hidden" id="{{$id}}" name="{{$name}}" value="{{ old($column, $value) }}">
        <a id="record_btn" class="btn btn-primary">Nhấn để bắt đầu</a>
        <div id="playlist"></div>
    </div>
</div>