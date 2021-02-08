@forelse($preguntas as $p)
    <div class="col s12 m6 l4">
        <div class="card" style="height: 400px ;max-height: 400px; min-height: 400px;">
            <div class="card-image">
                <div class="activator col s12" style="cursor: pointer">
                    <p class="activator cyan-text" style="font-size: 24px;font-weight: 300;">Pregunta</p>
                    <p class="activator" style="font-size: medium;font-weight: 300;">{{$p->pregunta}}</p>
                </div>
                @if(\Illuminate\Support\Facades\Auth::check())
                    @if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre == "superadministrador")
                        <div class="col s12 right-align">
                            <a href="#" onclick="showDeletePregunta({{$p->id}})"><i class="fa fa-trash red-text"></i></a>
                            <a href="#modal-editar-pregunta-frecuente" class="modal-trigger" onclick="showEditarPregunta({{$p->id}})"><i class="fa fa-edit cyan-text"></i></a>
                        </div>
                    @endif
                @endif
            </div>

            <div class="card-reveal scroll-style">
                <p class="card-title green-text">Respuesta</p>
                <p class="card-title" style="font-size: medium !important;">{{$p->respuesta}}</p>
                @if($p->enlace!='')
                    <p> Enlaces relacionados:
                        <a href="{!! $p->enlace !!}" target="_blank">{{ $p->enlace}}</a>
                    </p>
                @endif
                @if($p->embebido!='')
                    <iframe width="460" height="290" src="{{ $p->embebido }}" frameborder="0"></iframe>
                @endif
            </div>
        </div>
    </div>
@empty
    <p class="center-align">No se han encontrado resultados</p>
@endforelse