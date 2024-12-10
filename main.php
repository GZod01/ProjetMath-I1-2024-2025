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

$char_codes_list= mb_str_split($charlist);
$char_codes =[];

foreach($char_codes_list as $i=>$cc){
    $char_codes[$cc]=$i;
}
function showMultiply($matrix_a,$pair_b){
    global $char_codes_list;
    [[$a,$b],[$c,$d]]=$matrix_a;
    [$x,$y]=$pair_b;
    echo "<table border=1>";
    echo "<tr><td>$a</td><td>$b</td></tr>";
    echo "<tr><td>$c</td><td>$d</td></tr>";
    echo "</table>";
    echo "<table border=1>";
    echo "<tr><td>$x</td></tr>";
    echo "<tr><td>$y</td></tr>";
    echo "</table>";
    echo "<table border=1>";
    echo "<tr><td>".($a*$x+$b*$y)."</td></tr>";
    echo "<tr><td>".($c*$x+$d*$y)."</td></tr>";
    echo "</table>";
    echo "<table border=1>";
    echo "<tr><td>".$char_codes_list[mod27($a*$x+$b*$y)]."</td></tr>";
    echo "<tr><td>".$char_codes_list[mod27($c*$x+$d*$y)]."</td></tr>";
    echo "</table>";
}

function multiply(array $matrix_a, array $pair_b){
    showMultiply($matrix_a,$pair_b);
    [[$a,$b],[$c,$d]]=$matrix_a;
    $divide = 1/($a*$d-$b*$c);
    $a*=$divide;
    $b*=$divide;
    $c*=$divide;
    $d*=$divide;
    [$x,$y]=$pair_b;
    return [$a*$x+$b*$y,$c*$x+$d*$y];
}
function mod27($a){
    global $charlist;
    $mod_val=mb_strlen($charlist)-1;
    return fmod(($mod_val+(fmod($a,$mod_val))),$mod_val);
}


function encrypt($message,$base){
    global $char_codes,$char_codes_list;
    $message_encrypted="";
    foreach(mb_str_split($message,2) as $char){
        $chars = mb_str_split($char);
        $char_codes_pair = [$char_codes[$chars[0]],$char_codes[$chars[1]]];
        $char_codes_encrypted= multiply($base,$char_codes_pair);
        $char_codes_encrypted[0] = mod27($char_codes_encrypted[0]);
        $char_codes_encrypted[1] = mod27($char_codes_encrypted[1]);
        $message_encrypted.= $char_codes_list[$char_codes_encrypted[0]].$char_codes_list[$char_codes_encrypted[1]];
    }
    return $message_encrypted;
}

function decrypt($message,$decrypt_base){
    return encrypt($message,$decrypt_base);
}
$message;
$baseStr;
if(isset($_REQUEST["message"])){
    $baseArr = explode(",",$_REQUEST["base"]??"1,2,3,7");
    if(count($baseArr)!=4) $baseArr = [1,2,3,7];
    
    [$a,$b,$c,$d] = $baseArr;
    if($a*$d-$b*$c==0){
        echo "bad determinant can't be 0<br>\n";
        $baseArr = [1,2,3,7];
    }
    $baseStr=implode(",",$baseArr);
    
    $base = [[(int)$baseArr[0],(int)$baseArr[1]],[(int)$baseArr[2],(int)$baseArr[3]]];
    $baseInv = [[(int)$baseArr[3],-(int)$baseArr[1]],[-(int)$baseArr[2],(int)$baseArr[0]]];
    $message = $_REQUEST["message"];
    $n_message="";
    foreach(mb_str_split($message) as $char){
        if(!isset($char_codes[$char])) $char=iconv('UTF-8','ASCII//TRANSLIT',$char);
        if(!isset($char_codes[$char])) $char=" ";
        $n_message.=$char;
    }
    if(mb_strlen($message)%2==1) $n_message.=" ";
    $message=$n_message;
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