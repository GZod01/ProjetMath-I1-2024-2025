<?php
// Draft of matrix class
class matrix{
    private array $arrayval;
    public static function voidMatrixSize(int $size_x, int $size_y){
        $arr = [];
        for($i=0;$i<$size_x;$i++){
            for($j=0;$j<$size_y;$j++){
                
            }
        }
    }
    function __construct(array $arrayval=[[0,0],[0,0]],int $sizex=-1,int $sizey=-1)
    {
        if($sizex>-1 && count($arrayval)!=$sizex)throw new Exception("Invalid Matrix");
        if($sizey>-1) foreach($arrayval as $r) if(count($r)!=$sizey) throw new Exception("Invalid Matrix");
        $lengthsuper = count($arrayval);
        $this->arrayval = $arrayval;
    }
    function setIndex($i,$j,$value){
        $this->getArray()[$i][$j] = $value;
    }
    function getArray():array{
        return $this->arrayval;
    }
    function getRows():array{
        return $this->getArray();
    }
    function getColumns():array{
        $arrayreturn = [];
        foreach($this->getArray() as $i=>$row){
            foreach($row as $j=>$value){
                $arrayreturn[$j][$i] = $value;
            }
        }
        return $arrayreturn;
    }
    function multiply(matrix $b){
        die(var_dump($this->getRows(),$this->getColumns(),$b->getRows(),$b->getColumns()));
        $a = $this;
        $c = new matrix();
        foreach($a->getRows() as $i=>$row){

        }
    }
    function __toString()
    {
        $stringreturn = "";
        $cellsarray = [];
        foreach($this->getArray() as $r){
            
        }
    }
}
class matrix2{
    public int $a,$b,$c,$d;
    function __construct(array $arrayval=[[0,0],[0,0]])
    {
        if(count($arrayval)!=2 || count($arrayval[0])!=2 || count($arrayval[1])!=2) throw new Exception("Invalid");
        [[$a,$b],[$c,$d]] = $arrayval;
        $this->a=$a;
        $this->b=$b;
        $this->c=$c;
        $this->d=$b;
    }
    function toArray(){
        return [[$this->a,$this->b],[$this->c,$this->d]];
    }
    function det(){
        return ($this->a*$this->d)-($this->b*$this->c);
    }
    function multiply(matrix2 $b){
        $c = new matrix2();
        $c->a = $this->a*$b->a+$this->b*$b->c;
        $c->b = $this->a*$b->b+$this->b*$b->d;
        $c->c = $this->c*$b->a+$this->d*$b->c;
        $c->d = $this->c*$b->b+$this->d*$b->d;
        return $c;
    }
    function __toString()
    {
        $maxsize = max([strlen($this->a),strlen($this->b),strlen($this->c),strlen($this->d)]);
        $a = str_pad($this->a,$maxsize," ",STR_PAD_LEFT);
        $b = str_pad($this->b,$maxsize," ",STR_PAD_RIGHT);
        $c = str_pad($this->c,$maxsize," ",STR_PAD_LEFT);
        $d= str_pad($this->d,$maxsize," ",STR_PAD_RIGHT);
        $s = "|$a $b|\n|$c $d|";
        return $s;
    }
}