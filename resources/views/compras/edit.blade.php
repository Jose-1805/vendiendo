    <input type="hidden" name="id_compra" value="{{$id}}">
    <input type="hidden" name="valor_compra_saldo" id="valor_compra_saldo" value="{{$saldo}}">
    <input type="hidden" name="_token" id="general-token" value="{{csrf_token()}}">
    <?php
    $selected1 ="";$selected2 ="";
    $disabled1 = $disabled2 = '';
    if($compra->estado == "Recibida"){
        $selected1="selected";
        $disabled1 = 'disabled';
    }
    else{
        $selected2 = 'selected';
    }
    //if($compra->estado_pagar == "Recibida")$selected2="selected";
    ?>

    <div class="input-field col s6">
        <select name="estado" class="active" {{$disabled1}}>
            <option value="Recibida" <?php echo $selected1;?>>Recibida</option>
            <option value="Pendiente por recibir" <?php echo $selected2;?>>Pendiente por recibir</option>
        </select>
        <label>Seleccione estado compra</label>
    </div>
    <?php
    $selected1 ="";$selected2 ="";
     if($compra->estado_pago == "Pagada"){
         $selected1="selected";
         $disabled2 = 'disabled';
     }else
         $selected2='selected';
    ?>
    <div class="input-field col s6">
        <select name="estado_pago" id="estado_pago" class="active" {{ $disabled2 }}>
            <option value="Pagada" <?php echo $selected1;?>>Pagada</option>
            <option value="Pendiente por pagar" <?php echo $selected2;?>>Pendiente por pagar</option>
        </select>
        <label>Seleccione estado pago</label>
    </div>