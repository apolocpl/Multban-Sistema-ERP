
@if ($errors->any())
<div class="row">
  <div class="col-md-12">
      <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h5><i class="icon fas fa-ban"></i> Alerta!</h5>
          <ul>
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
      @endif

      @if ($message = Session::get('success'))
      <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
          {{$message}}
      </div>

  </div>
</div>
@endif


<div class="row" id="row-top" style="">
  <div class=" navbar-fixed-top fixed nav-opcoes">

    <!-- FAIXA DE OPÇÕES SALVAR -->
    <button id="btnSalvar" type="button" class="btn btn-primary btn-sm"><i class="icon fas fa-save"></i> Salvar</button>

    @if (!request()->is('*/inserir'))
        <!-- FAIXA DE OPÇÕES INATIVAR -->
        <button id="btnInativar" type="button" class="btn btn-primary btn-sm"><i class="icon fas fa-times"></i> Inativar</button>
        <!-- FAIXA DE OPÇÕES BLOQUEAR -->
        <button id="btnExcluir" type="button" class="btn btn-primary btn-sm"><i class="icon fas fa-lock"></i> Bloquear</button>
    @endif

    <!-- FAIXA DE OPÇÕES VOLTAR -->
    <button id="btnCancelar" onclick="location.href='{{url($route)}}'" type="button" class="btnCancelar btn btn-secundary-multban btn-sm"><i class="icon fas fa-arrow-left"></i> Voltar</button>



  </div>
</div>
