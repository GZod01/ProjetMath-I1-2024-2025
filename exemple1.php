<?php
//initial : $charlist = "abcdefghijklmnopqrstuvwxyz ";

$charlist = "abcdefghijklmnopqrstuvwxyz &é(-è_çà)=$1234567890?.!,;:*/+-";
$charcodeslist= str_split($charlist);
$charcodes =[];

foreach($charcodeslist as $i=>$cc){
    $charcodes[$cc]=$i;
}
// print_r($charcodes);
// var_dump(27+-1%27);
function multiply(array $matrix_a, array $pair_b){
    [[$a,$b],[$c,$d]]=$matrix_a;
    [$x,$y]=$pair_b;
    return [$a*$x+$b*$y,$c*$x+$d*$y];
}
function mod27($a){
    global $charlist;
    return (strlen($charlist)+($a%strlen($charlist)))%strlen($charlist);
}


function encrypt($message,$base){
    global $charcodes,$charcodeslist;
    $message_encrypted="";
    $message = strtolower($message);
    foreach(str_split($message,2) as $char){
        $chars = str_split($char);
        if(!isset($chars[1])) $chars[1]=" ";
        if($chars[1]=="")$chars[1]=" ";
        $charcodes_pair = [$charcodes[$chars[0]],$charcodes[$chars[1]]];
        // print_r($charcodes_pair);
        // print_r($chars[1]);
        $charcodes_encrypted= multiply($base,$charcodes_pair);
        // print_r($charcodes_encrypted);
        $charcodes_encrypted[0] = mod27($charcodes_encrypted[0]);
        $charcodes_encrypted[1] = mod27($charcodes_encrypted[1]);
        // print_r($charcodes_encrypted);
        // print_r($charcodes_encrypted);
        $message_encrypted.= $charcodeslist[$charcodes_encrypted[0]].$charcodeslist[$charcodes_encrypted[1]];
        // echo $message_encrypted;
    }
    return $message_encrypted;
}
function decrypt($message,$decryptbase){
    global $charcodes,$charcodeslist;
    $message_decrypted="";
    foreach(str_split($message,2) as $char){
        $chars = str_split($char);
        if(!isset($chars[1])) $chars[1]=" ";
        if($chars[1]=="")$chars[1]=" ";
        $charcodes_pair = [$charcodes[$chars[0]],$charcodes[$chars[1]]];
        // print_r($charcodes_pair);
        $charcodes_decrypted = multiply($decryptbase,$charcodes_pair);
        // print_r($charcodes_decrypted);
        $charcodes_decrypted[0] = mod27($charcodes_decrypted[0]);
        $charcodes_decrypted[1] = mod27($charcodes_decrypted[1]);
        // print_r($charcodes_decrypted);
        $message_decrypted.= $charcodeslist[$charcodes_decrypted[0]].$charcodeslist[$charcodes_decrypted[1]];
    }
    return $message_decrypted;
}

if(isset($_POST["message"])){
    $baseArr = str_split($_POST["base"]??"1237");
    if(count($baseArr)!=4) $baseArr = [1,2,3,7];
    {
        [$a,$b,$c,$d] = $baseArr;
        if($a*$d-$b*$c!=1){
            echo "bad determinant<br>\n";
            $baseArr = [1,2,3,7];
        }
    }
    $base = [[(int)$baseArr[0],(int)$baseArr[1]],[(int)$baseArr[2],(int)$baseArr[3]]];
    $baseInv = [[(int)$baseArr[3],-(int)$baseArr[1]],[-(int)$baseArr[2],(int)$baseArr[0]]];
    $message = $_POST["message"];
    $action = $_POST["action"]??"encrypt";
    if($action=="decrypt"){
        echo decrypt($message,$baseInv);
    }else{
        echo encrypt($message,$base);
    }
}
?>
<form action="" method="post">
    <input type=text name="message" value="<?=htmlspecialchars($_POST["message"]??"")?>">
    <input type=text name="base" value="<?=htmlspecialchars($_POST["base"]??"1237")?>" required=false optional>
    <input type=submit name="action" value="encrypt">
    <input type=submit name="action" value="decrypt">
</form>