<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type:text/html;charset=utf-8');
mb_internal_encoding("utf-8");
?>
<meta charset="utf-8">
<?php





$charlist = " ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz&é(-è_çà)=$1234567890?.!,;:*/\\+-'\"#{[|@]}%µ§";

$charcodeslist= mb_str_split($charlist);
$charcodes =[];

foreach($charcodeslist as $i=>$cc){
    $charcodes[$cc]=$i;
}
function multiply(array $matrix_a, array $pair_b){
    [[$a,$b],[$c,$d]]=$matrix_a;
    [$x,$y]=$pair_b;
    return [$a*$x+$b*$y,$c*$x+$d*$y];
}
function mod27($a){
    global $charlist;
    $modval=mb_strlen($charlist)-1;
    return ($modval+($a%$modval))%$modval;
}


function encrypt($message,$base){
    global $charcodes,$charcodeslist;
    $message_encrypted="";
    foreach(mb_str_split($message,2) as $char){
        $chars = mb_str_split($char);
        $charcodes_pair = [$charcodes[$chars[0]],$charcodes[$chars[1]]];
        $charcodes_encrypted= multiply($base,$charcodes_pair);
        $charcodes_encrypted[0] = mod27($charcodes_encrypted[0]);
        $charcodes_encrypted[1] = mod27($charcodes_encrypted[1]);
        $message_encrypted.= $charcodeslist[$charcodes_encrypted[0]].$charcodeslist[$charcodes_encrypted[1]];
    }
    return $message_encrypted;
}
function decrypt($message,$decryptbase){
    return encrypt($message,$decryptbase);
}
$message;
$baseStr;
if(isset($_REQUEST["message"])){
    $baseArr = explode(",",$_REQUEST["base"]??"1,2,3,7");
    if(count($baseArr)!=4) $baseArr = [1,2,3,7];
    
    [$a,$b,$c,$d] = $baseArr;
    if($a*$d-$b*$c!=1){
        echo "bad determinant<br>\n";
        $baseArr = [1,2,3,7];
    }
    $baseStr=implode("",$baseArr);
    
    $base = [[(int)$baseArr[0],(int)$baseArr[1]],[(int)$baseArr[2],(int)$baseArr[3]]];
    $baseInv = [[(int)$baseArr[3],-(int)$baseArr[1]],[-(int)$baseArr[2],(int)$baseArr[0]]];
    $message = $_REQUEST["message"];
    $nmessage="";
    foreach(mb_str_split($message) as $char){
        if(!isset($charcodes[$char])) $char=iconv('UTF-8','ASCII//TRANSLIT',$char);
        if(!isset($charcodes[$char])) $char=" ";
    }
    if(mb_strlen($message)%2==1) $nmessage.=" ";
    $message=$nmessage;
    $action = $_REQUEST["action"]??"encrypt";
    echo "<p><pre>";
    if($action=="decrypt"){
        echo decrypt($message,$baseInv);
    }else{
        echo encrypt($message,$base);
    }
    echo "</pre></p>";
}
?>
<form action="" method="get">
    <input type=text name="message" value="<?=htmlspecialchars($message??"")?>">
    <input type=text name="base" value="<?=htmlspecialchars($baseStr??"1,2,3,7")?>" required=false optional>
    <input type=submit name="action" value="encrypt">
    <input type=submit name="action" value="decrypt">
</form>