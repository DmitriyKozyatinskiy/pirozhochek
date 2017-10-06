@extends('layouts.app')

@section('content')
  <div class="container">
    <div class="row">
      <div class="col-md-8 ml-md-auto FormWrapper">
        <h2 class="panel-heading">Загрузка товаров</h2>
        @if (session('confirmation-success'))
          <div class="alert alert-success">
            {{ session('confirmation-success') }}
          </div>
        @endif
        @if (session('confirmation-danger'))
          <div class="alert alert-danger">
            {!! session('confirmation-danger') !!}
          </div>
        @endif

        <form role="form" method="POST" enctype="multipart/form-data" action="{{ url('/products/upload') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="custom-control custom-radio">
              <input id="js-cats-type" value="cats" name="type"
                     type="radio" class="custom-control-input" checked required>
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Котики</span>
            </label>
            <label class="custom-control custom-radio">
              <input id="js-dogs-type" value="dogs" name="type"
                     type="radio" class="custom-control-input" required>
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Пёсики</span>
            </label>
          </div>

          <div class="form-group">
            <label class="custom-file">
              <input name="products" type="file" id="js-file" class="custom-file-input" required>
              <span class="custom-file-control"></span>
            </label>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-primary">Загрузить</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
