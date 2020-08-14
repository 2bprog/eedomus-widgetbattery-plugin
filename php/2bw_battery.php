<?

/*
 Fichier : 2bw_battery.php 
 version : 0.0.7
 auteur  : Benjamin B. - benj70b
 github  : https://github.com/2bprog/eedomus-widgetbattery-plugin
 
*/

$idsrc = getarg('id',false,0);
$max = getarg('max', false, 100);
$nbitems = 0;

$titrevalue = '';
if ($idsrc != 0 )
{
    $src = getPeriphList(false, $idsrc);
    if (count($src) == 1)
    {
        $fullname = $src[$idsrc]['full_name'];
        $roomname = $src[$idsrc]['room_label'];
        
        $titrevalue = trim(sdk_removeifatend($fullname, $roomname));
    }
}

if ($titrevalue == '') 
    $titrevalue = "Etat des batteries";

// lecture des infos
$inf = new sdk_eedclientinfo($_SERVER, $_GET); 
$idt = time(); // ID aléatoire

$idload = $idt."load";
$idmain = $idt."main";
$idtitre = $idt."titre";
$idlist = $idt."list";

$fontsize = 'w3-tiny';
$fontcolor = '#666666';
$fontcolorbw = '#444444';
$titrecolor = 'rgba(0, 0, 0, 0.87)';
$titrecolorbw = 'rgba(255, 255, 255, 0.8)';
$fontname = 'tahoma,arial,helvetica,sans-serif';
$titredisplay = 'display:none;';
//$titredisplay = '';

if ($inf->portail === false)
{
    $titredisplay = '';
    $fontsize = 'font14';
    $fontname = '"RobotoDraft","Roboto","Helvetica Neue",sans-serif';
    $fontcolor = 'rgba(0, 0, 0, 0.54)';
    $fontcolorbw = 'rgba(255, 255, 255, 0.64)';
}

// recuperation des informations batterie
$jsbat =  sdk_getjsbatteries($inf,$max, $nbitems);

?>    

<? 
// ******************************************
//  HTML - START
// ******************************************
?>

<html>
<head>
<title>2B - Batteries Widget</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


<style>
    .load {
        z-index: 100;
    }
    .txtcolor {
        color:<? echo $fontcolor; ?>;
        font-family: <? echo $fontname; ?>  !important;
    }

    .nopadding     {
        padding: 0px !important;
    }
    .padding4l     {
        padding-left: 4px !important;
    }
    .padding4r     {
        padding-right: 2px !important;
    }
    .ellipsis    {
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }  
    .font14 {
        font-size:14px ! important;
    }
    .imgbat {
        width:24px;
        margin-right:8px;
        margin-left:8px
    }
    
    .w3-button
    {
        cursor: pointer;
    }
    
    .Titre {
        color:<? echo $titrecolor; ?>;
        font-size: : <? echo ($inf->portail) ? '11' : '16'; ?> px !important;
        font-weight: 700 !important;
        text-overflow: ellipsis !important;
        overflow: hidden !important;
        
    }
    
    .pointer {cursor: pointer;}
    
     .wait {
        color:rgb(127, 127, 127) !important;
     }
</style>    
</head>


<body class="txtcolor" style="background-color: transparent;" scrolling="no">

<div class="w3-container load" id="<? echo $idload; ?>">
    <div class="w3-display-container" style="height:100%;">
        <div class="w3-display-middle wait <? echo $fontsize; ?> ">
            <i class="fa fa-spinner fa-spin"></i> Chargement...
        </div>
    </div>
</div>

<div class="w3-container nopadding  txtcolor <? echo $fontsize; ?>" id="<? echo $idmain; ?>" style="display:none">
  
    <div class="w3-top">
        <div class="w3-bar nopadding titre" id="<? echo $idtitre; ?>" style="<? echo $titredisplay; ?>" >
            <div class="w3-bar-item w3-padding-small"><? echo $titrevalue; ?>
            </div>
        </div>    
    </div>

    <div class="w3-container nopadding 2bwpanel" style="overflow:auto;">    
        <ul id="<? echo $idlist; ?>" class="w3-ul" style="" ></ul>
    </div>

</div>
    
<script>

<? 
	$noperiph = 'Aucun périphérique trouvé.';
    if ($inf->portailmobile === true || $inf->portaillocal === true)
      $noperiph = utf8_encode($noperiph);          
?>

// variables
var _2bportail = <? echo $inf->portail ? "true" : "false"; ?>;

var _2bjsbat = '<? echo $jsbat; ?>';

var _2bwidload = '<? echo $idload; ?>';
var _2bwidmain =  '<? echo $idmain; ?>';
var _2bwidtitre = '<? echo $idtitre; ?>';
var _2bwidlist = '<? echo $idlist; ?>';

var _2bweltload = document.getElementById(_2bwidload);;
var _2bweltmain = document.getElementById(_2bwidmain);;
var _2bwelttitre = document.getElementById(_2bwidtitre);;
var _2bweltlist = document.getElementById(_2bwidlist);;

var _2bmarge = <? echo $inf->portail ? 2 : 0; ?>;
var _2brequesturi = '<?echo $inf->sdk_getvar('REQUEST_URI'); ?>' ;

var _2bdarktheme = <? echo $inf->darktheme ? "true" : "false"; ?>;
var _2bwfontcolorbw = '<? echo $fontcolorbw; ?>';
var _2bwtitrecolorbw = '<? echo $titrecolorbw; ?>';

var _2bnoperiph = '<? echo $noperiph; ?>';


function rgbtext2val(rgb)
{
    var a = rgb.split("(")[1].split(")")[0];
    a = a.split(",");
    var b=a.map(function(x) { //For each array element
         x = parseInt(x).toString(16); //Convert to a base16 string
        return (x.length == 1) ? "0" + x : x; //Add zero if we get only one character
        });
    b = "0x" + b.join("");

    return eval(b);
}


/*
function setheight(totalHeight, headerHeight, mainElement, panelclass)

fixe la hauteur des elements en fonction de la hauteur d'une entete (optionnel)
(cela permet de mettre en place du scroll si les classes utilise overflow)
pour totalHeight utiliser visualViewport.height
*/
function eedw_setheight(totalHeight, headerHeight, mainElement, panelclass, marge)
{

    document.body.style.height = totalHeight;
    if (mainElement !== null) mainElement.style.height = totalHeight;
    
    var i;
    var x = document.getElementsByClassName(panelclass);
    for (i = 0; i < x.length; i++) 
    {
        x[i].style.position  = "absolute";
        x[i].style.top = headerHeight;
        x[i].style.height = totalHeight - (headerHeight  + marge);
        x[i].style.width = "100%";
    }
}

/*
function eedw_adjustiframe(brequesturi)

ajustment de l'iframe en largeur à 100% 
ceci s'effectue avec une recherche sur l'url du widget
en php => $_SERVER['REQUEST_URI']
*/
function eedw_adjustiframe(requesturi)
{
    
    if (window.parent !== null)
    {
        Array.prototype.forEach.call(window.parent.document.querySelectorAll("iframe"), 
        function(iframe) 
        {
            // recup iframe
            const url = new URL(iframe.src);
            var framesrc = url.pathname + url.search;
            if ( framesrc === requesturi )
            {
                // trouvé => redimentionnement + sauvegarde de la hauteur
                iframe.style.width='100%';
                return;
              }
        });
    }
}

// au chargement
window.onresize = function() 
{
    eedw_setheight( visualViewport.height, _2bwelttitre.offsetHeight, _2bweltmain, '2bwpanel', _2bmarge);
}


// au chargement
window.onload = function() 
{
    var darktheme = _2bdarktheme;
    var backcolor = '';
    if (darktheme == false && (window.parent !== null) && _2bportail === false)  
    {
        var backcolor = window.getComputedStyle( window.parent.document.body ,null).getPropertyValue('background-color');
        darktheme = (rgbtext2val(backcolor) === 0);
    }
    
    // ajustement de l'iframe si besoin
    eedw_adjustiframe(_2brequesturi);

    if (_2bportail === false)
    { 
         if (darktheme !== true) 
            darktheme = (rgbtext2val(backcolor) === 0);
    
        if (backcolor == '') 
            backcolor = 'rgb(0,0,0)';
    
        if (darktheme !== false)
        {
            document.body.style.color = _2bwfontcolorbw;
            _2bweltload.style.color = _2bwfontcolorbw;
            _2bweltmain.style.color = _2bwfontcolorbw;
            _2bwelttitre.style.color = _2bwtitrecolorbw;
        }
        
        
        document.body.style.backgroundColor = backcolor;
        _2bweltload.style.backgroundColor = backcolor;
        _2bweltmain.style.backgroundColor = backcolor;
        _2bwelttitre.style.backgroundColor = backcolor;
    }
    
    var eids = JSON.parse(_2bjsbat);    
    eids.sort(function(a, b)
     {
         var r =  a.bat - b.bat;
         if (r == 0) r = a.name.localeCompare(b.name);
         return r;
         
     });
    
    var nb = eids.length;  
    var innerhtml = '';
    for (i=0;i<nb;i++)
    {
        
        var eid = eids[i].id;
        var bat = eids[i].bat;
        var batimg = 0;
        if (bat < 0) bat = 0;
        if (bat > 100) bat = 100;
        
      // id":"'.$eid.'" ,"bat":'.$ebat.' ,"name
        var item = '<li class="w3-padding-small">'
        item = item + '<div class="w3-row nopadding ">';
        
        item = item + '<div class="w3-col w3-left  nopadding w3-right-align " style="width:40px"><b>';
        item = item + bat.toString() + '%</b></div>'; // Battery

        item = item + '<div class="w3-rest  nopadding  ellipsis">';
        
        if (_2bportail===true) item = item + '<a class="pointer" onclick="javascript:window.parent.showConfig(true, ' + eid.toString() + ');">';
        
        item = item + '<img class="imgbat" src="https://m.eedomus.com/img/battery_';
        batimg = 0;
        if (bat > 0)
        {
            batimg = Math.floor((bat + 25) / 25);
            if (batimg > 4) batimg = 4;
        }
        item = item + batimg.toString();
        item = item + '.png">';
        
        if (_2bportail===true) item = item + '</a>';
        
        item = item + eids[i].name;
        item = item + '</div></div></li>';

        innerhtml = innerhtml + item;
        
    }
    if (nb == 0)
    {            
        var item = '<li class="w3-padding-small w3-center">'
        item = item + '<p>' + _2bnoperiph + '</p>';
        item = item + '</li>'; 
        innerhtml = item;
    }
    
    _2bweltlist.innerHTML  = innerhtml;
    
    // force l'affichage pour avoir la hauteur du titre  
    _2bweltmain.style.display = "block";  
    
    eedw_setheight( visualViewport.height, _2bwelttitre.offsetHeight, _2bweltmain, '2bwpanel', _2bmarge);
    
    _2bweltload.style.display = "none";
    
};    

</script>
</body>
<html>

<? 
// ******************************************
//  HTML - END
// ******************************************
?>
<?

function sdk_removeifatend($source, $atend)
{
    $ret = $source;
    if (substr($source,-strlen($atend))===$atend) 
        $ret = substr($source, 0, strlen($source)-strlen($atend));
    return $ret;
}

function sdk_getjsbatteries($inf,$maxlevel, &$nbitems)
{
    
 
    $spid = 'parent_device_id';
    $sid = 'device_id';
    $sname ='full_name';
    $eeids = getPeriphList();

    
    // parcourt du resultat pour obtenir les indicateurs de batteries
    $jsret = '[ ';
    $inb = 0;
    foreach ($eeids as $key => $value)
    {
        $pid = $value[$spid];
        $eid = $value[$sid];
        if ($pid === '' || $pid === null || $pid=== $eid) 
        {
            $ebat = $value['battery'];
            if ($ebat !== ''  && intval($ebat) <= $maxlevel)
            //if ($ebat === '')
              //  $ebat=0;
            {
                $sep = ',';
                
                $ename = htmlspecialchars($value[$sname]);
                if ($inf->portailmobile === true || $inf->portaillocal === true)
                    $ename = utf8_encode($ename);
                
    
                if ($inb === 0) $sep='';
                
                //$ename = str_replace ( "\\","\\\\", $ename);
                $ename = str_replace ( "'","\\'", $ename);
            	$ename = str_replace ( "\\\\'","\\'", $ename);
                $jsret = $jsret.$sep.'{ "id":"'.$eid.'" ,"bat":'.$ebat.' ,"name":"'.$ename.'" }';
                $inb ++;
            }
        }
    }
    
    $nbitems = $inb;
    $jsret = $jsret.']';
    //$jsret = str_replace ( "'","\\'", $jsret);
	//$jsret = str_replace ( "\\\\'","\\'", $jsret);
    return $jsret; 
}



class sdk_eedclientinfo
{
   
   private $_svars= array();
   private $_args = array();
   public $portail  = false;
   public $portailmobile  = false;
   public $portaillocal  = false;
   public $android  = false;
   public $ios  = false;
   public $inlocalnet  = false;
   public $darktheme = false;
   public $height = -1;

  // $svars  = $_SERVERS
  // $args = $_GET
    public function __construct($svars, $args)
    {
        $this->_svars =  $svars;
        $this->_args = $args;
        
        $host= $this->sdk_getvar('HTTP_HOST');
        $remote = $this->sdk_getvar('REMOTE_ADDR');
        $appmobile = $this->sdk_getarg('mode');
        $typemobile = $this->sdk_getarg('app');
        $this->darktheme  = $this->sdk_getarg('dark_theme') == '1';
        
        $this->height = $this->sdk_getarg('SMARTPHONE_HEIGHT');
        if ($this->height === '') $this->height = -1;
        
        // portail 'normal'
        $this->portail = strpos($host, 'secure.eedomus') !== false;
        if ($this->portail === false) 
        {
            // portail mobile
            $this->portailmobile = (strpos($host, 'm.eedomus.') !== false);
            if ($this->portailmobile === false)
            {
                // Appli android ?
                $this->android = ($appmobile == 'mobile') && ($typemobile == 'android');
                if ($this->android === false)
                {
                    // Appli IOS ?
                    $this->ios = ($appmobile == 'mobile') && ($typemobile == 'ios');
                }
            }
        }
        
        if ($this->portail === false &&  $this->portailmobile== false &&
            $this->android== false && $this->ios== false)
        {
            //  portail  local ?
            $this->portaillocal = $this->sdk_checkprivatenet($host);
            if ($this->portaillocal === false)
                $this->portail = true; // force portail car pas trouvé !
                
        }
        
        // client en local 
        $this->inlocalnet = $this->sdk_checkprivatenet($remote);
      
        
    }
    
   public function sdk_getvar($item)
   {
        $ret = '';
        if (isset($this->_svars[$item]))  $ret = strtolower($this->_svars[$item]);
        return $ret;
   }

   public function sdk_getarg($item)
   {
       $ret = '';
        if (isset($this->_args[$item]))  $ret = strtolower($this->_args[$item]);
        return $ret;
   }
      
   public function sdk_checkprivatenet($ip)
    {
        $ret = false;
        $nets = array ("127.0.0.1", "192.168.","172.16.", '10.', 'localhost');
        for ($i=17; $i <32; $i++)
        {
            $net[] = '172.'.$i.'.';
        }
        foreach ($nets as $net)
        {
            $ret = substr( $ip, 0, strlen($net) ) === $net;
            if ($ret === true) break;
        }
        return $ret;
    }
    
    public function sdk_dumpall()
    {
        echo "_svars : " ;       var_dump($this->_svars);
        echo "_args : ";        var_dump($this->_args);
        $this->sdk_dumpindic();
    }
    
    public function sdk_dumpindic()
    {
        echo "portail : ";          var_dump($this->portail);
        echo "portailmobile : ";    var_dump($this->portailmobile);
        echo "portaillocal : ";     var_dump($this->portaillocal);
        echo "android : ";          var_dump($this->android);
        echo "ios : ";              var_dump($this->ios);
		echo "darktheme : ";        var_dump($this->darktheme);
        echo "inlocalnet : ";       var_dump($this->inlocalnet);
        echo "height : ";        var_dump($this->height);
        
    }
    

}


?>
