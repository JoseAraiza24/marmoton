x<?php
	function conectar (){
		$Server = "127.0.0.1";
	    $User = "root";
	    $Pwd = "";
	    $BD = "marmoton";
	    $Conexion = mysqli_connect($Server, $User, $Pwd, $BD);
	    return $Conexion;
	}

	function ejecutar ($Conexion, $SQL){
		$ResultSet = mysqli_query($Conexion, $SQL)
			or die(mysqli_error($Conexion));
		return $ResultSet;
	}

	function procesar (){

	}

	function desconectar ($Conexion){
		$Resultado = mysqli_close($Conexion);
		return $Resultado;
	}
?> 