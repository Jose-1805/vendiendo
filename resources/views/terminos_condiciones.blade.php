@extends("templates.master")

@section('titulo')
    Vendiendo.co - Inicio
@stop

@section('css')
@parent
    <link rel="stylesheet" href="{{ asset('css/inicio.css') }}">
@stop

@section('contenido')
    <div id="modal1" class="modal modal-fixed-footer">
        <div class="modal-content">
            @include('terminos_condiciones_text')
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-action waves-effect btn-flat red-text" id="no_acepta">No acepto</a>
            <a href="#!" class="modal-action waves-effect btn-flat green-text" id="acepta">Acepto</a>
        </div>
    </div>
@stop


@section('js')
    @parent
    <script src="{{asset('/js/terminos_condiciones.js')}}"></script>
    <script>
        $(function(){
            $("#modal1").openModal({
                dismissible: false, // Modal can be dismissed by clicking outside of the modal
                opacity: .8
            });
        })
    </script>
@endsection