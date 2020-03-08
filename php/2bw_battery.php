<?

/*
 Fichier : 2bw_bat.php 
 version : 0.0.0
 auteur  : benj70b
 github  : https://github.com/2bprog/eedomus-widgetbattery-plugin
*/

$idsrc = getarg('id',false,0);
$max = getarg('max', false, 100);

/*
$titrevalue = '';
if ($idsrc != 0 )
{
    $src = getValue($idsrc);
    var_dump($src);
    die;
    if ($src !== null) $titrevalue = $src['full_name'];
}
if ($titrevalue == '')  */
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
$fontname = 'tahoma,arial,helvetica,sans-serif';
$titredisplay = 'display:none;';
$titreheight = 0;
//if ($inf->portailmobile === true || $inf->portaillocal === true)
if ($inf->portail === false)
{
    $titreheight = 30;
    $titredisplay = '';
    $fontsize = 'font14';
    $fontname = '"RobotoDraft","Roboto","Helvetica Neue",sans-serif';
    $fontcolor = 'rgba(0, 0, 0, 0.54);';
}

// recuperation des informations batterie
$jsbat =  sdk_getjsbatteries($inf,$max);

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
    .txtcolor {
        color:<? echo $fontcolor ?> !important;
        font-family: <? echo $fontname ?>  !important;
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
    
    .Titre {
        color:rgba(0, 0, 0, 0.87) !important;
        font-size: 16px !important;
        font-weight: 700 !important;
        text-overflow: ellipsis !important;
        overflow: hidden !important;
    }
</style>    
</head>


<body class="txtcolor">

<div class="w3-container" id="<? echo $idload ?>">
    <div class="w3-display-container" style="height:100%;">
        <div class="w3-display-middle">
            <i class="fa fa-spinner fa-spin"></i> Chargement...
        </div>
    </div>
</div>

<div class="w3-container nopadding  txtcolor <? echo $fontsize ?>" id="<? echo $idmain ?>" style="display:none">
    <div class="w3-display-container nopadding" style="<? echo $titredisplay ?>height:<? echo $titreheight ?>px">
        <div class="w3-display-left nopadding titre">
            <? echo $titrevalue ?>
        </div>
    </div>
    <ul class="w3-ul" style="overflow:auto;" id="<? echo $idlist ?>">
    </ul>    
</div>

<script>



// au chargement
window.onload = function() 
{
    // affichage et redimentionnement de l'iframe
    Array.prototype.forEach.call(window.parent.document.querySelectorAll("iframe"), 
        function(iframe) 
        {
            // recup iframe
            const url = new URL(iframe.src);
            var framesrc = url.pathname + url.search;
            if ( framesrc === '<? echo $inf->sdk_getvar('REQUEST_URI'); ?>')
            {
                // trouvé => redimentionnement
                iframe.style.width='100%';
                //iframe.style.marginRight='2px';
            
                // recup id element et affichage
                var uil = document.getElementById('<? echo $idlist ?>');
                var utitle = document.getElementById('<? echo $idtitre ?>');
                
                uil.style.height = iframe.height - (<? echo $titreheight ?> + 2);
                
                return;
              }
        });
        
    var uil = document.getElementById('<? echo $idlist ?>');
    if (uil !== null)
    {
        var eids = JSON.parse('<? echo $jsbat; ?>');    
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
            
            var bat = eids[i].bat;
            if (bat < 0) bat = 0;
            if (bat > 100) bat = 100;
            
          // id":"'.$eid.'" ,"bat":'.$ebat.' ,"name
            var item = '<li class="w3-padding-small">'
            item = item + '<div class="w3-row nopadding ">';
            
            item = item + '<div class="w3-col w3-left  nopadding w3-right-align " style="width:40px"><b>';
            item = item + bat.toString() + '%</b></div>'; // Battery
   
            item = item + '<div class="w3-rest  nopadding  ellipsis">';
            item = item + '<img class="imgbat" src="https://m.eedomus.com/img/battery_';
            item = item + Math.floor((bat+20) / 25).toString();
            item = item + '.png">';
            item = item + eids[i].name;
            item = item + '</div></div></li>';
    
            innerhtml = innerhtml + item;
            
        }
        uil.innerHTML  = innerhtml;
    }
    
    
    document.getElementById("<? echo $idmain ?>").style.display = "block";
    document.getElementById("<? echo $idload ?>").style.display = "none";  

    
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

function sdk_getjsbatteries($inf,$maxlevel)
{
    if ($inf->portaillocal || $inf->inlocalnet)
    {
        $spid = 'parent_periph_id';
        $sid = 'periph_id';
        $sname ='name';
        
        $url = 'http://localhost/api/get?action=periph.list';
        $result = httpQuery($url, 'GET' , ''); 
        $result = str_replace ( '\"' , ' ' , $result);
        $result = sdk_json_decode($result, false);
        $eeids = $result['body'];
    }
    else
    {
        $spid = 'parent_device_id';
        $sid = 'device_id';
        $sname ='full_name';
        $eeids = getPeriphList();
    }
    
    // parcourt du resultat pour obtenir les indicateurs de batteries
    $jsret = '[ ';
    $inb = 0;
    foreach ($eeids as $key => $value)
    {
        $pid = $value[$spid];
        $ebat = $value['battery'];
        if ( ($pid === '' || $pid === null) && ($ebat !== '' && $ebat <= $maxlevel))
        {
            $sep = ',';
            $eid = $value[$sid];
            $ename = htmlspecialchars($value[$sname]);
            if ($inf->portailmobile === true) // || $inf->portaillocal === true)
                $ename = utf8_encode($ename);
            

            if ($inb === 0) $sep='';
            
            $jsret = $jsret.$sep.'{ "id":"'.$eid.'" ,"bat":'.$ebat.' ,"name":"'.$ename.'" }';
            $inb ++;
        }
    }
    
    $jsret = $jsret.']';
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
    }
    

}


?>
