<?php
    $tooltip = "";
    if(array_key_exists("tooltip",$data))$tooltip = $data["tooltip"];
?>
@if(!array_key_exists('btn-footer',$data) && !array_key_exists('btn-menu-toggle',$data))
<a class="item-menu col {{$data['responsive_class']}}" href="{{url($data['href'])}}" title="{{$tooltip}}">
    @if(array_key_exists('color_class',$data))
        <div class="circulo-item-menu {{$data['color_class']}}">
    @elseif(array_key_exists('color_style',$data))
        <div class="circulo-item-menu" style="background-color: {{$data['color_style']}}">
    @endif
        @if(array_key_exists('src',$data) && $data["src"] != "")
            <img class="img-item-menu" src="{{asset($data['src'])}}" alt="1" />
        @elseif(array_key_exists('fa_class',$data) && $data["fa_class"] != "")
            <i class="fa {{$data['fa_class']}}" aria-hidden="true" style="font-size: 25px;"></i>
        @else
            <i class="fa fa-question-circle" aria-hidden="true" style="font-size: 25px;"></i>
        @endif
    </div>
    <span class="label-item-menu">{{$data['label']}}</span>
</a>
@elseif(array_key_exists('btn-menu-toggle',$data))
    <div class="item-menu-app col {{$data["responsive_class"]}}" title="{{$tooltip}}">
        <div class="contenedor-img-item {{$data["color_class"]}}" data-url="{{url($data["href"])}}">
            @if(array_key_exists('src',$data) && $data["src"] != "")
                <img class="" src="{{asset($data['src'])}}" alt="1" />
            @elseif(array_key_exists('fa_class',$data) && $data["fa_class"] != "")
                <i class="fa {{$data['fa_class']}} white-text" aria-hidden="true" style="font-size: 20px;"></i>
            @else
                <i class="fa fa-question-circle white-text" aria-hidden="true" style="font-size: 20px;"></i>
            @endif
        </div>
        <span class="truncate">{{$data["label"]}}</span>
    </div>
@else
    <a class="item-menu btn-footer-menu" href="{{url($data['href'])}}" title="{{$tooltip}}">
        @if(array_key_exists('color_class',$data))
            <div class="circulo-item-menu {{$data['color_class']}}">
        @elseif(array_key_exists('color_style',$data))
            <div class="circulo-item-menu" style="background-color: {{$data['color_style']}}">
        @endif
                @if(array_key_exists('src',$data) && $data["src"] != "")
                    <img class="img-item-menu" src="{{asset($data['src'])}}" alt="1" />
                @elseif(array_key_exists('fa_class',$data) && $data["fa_class"] != "")
                    <i class="fa {{$data['fa_class']}}" aria-hidden="true"></i>
                @else
                    <i class="fa fa-question-circle" aria-hidden="true"></i>
                @endif
            </div>
    </a>

@endif