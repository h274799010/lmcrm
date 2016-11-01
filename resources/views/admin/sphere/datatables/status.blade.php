<div class="">
    <div class="togglebutton">
      <label>
        <input class="sphereChangeStatus" type="checkbox" value="{{ $model->id }}" @if($model->status) checked="checked" @endif {{--disabled="disabled"--}}>
        <span class="status">@if($model->status) @lang('admin/admin.yes') @else @lang('admin/admin.no') @endif</span>
      </label>
  </div>
</div>
<script type="text/javascript">$.material.init(".togglebutton")</script>