<?php
if (!defined('ABSPATH'))
    exit;

add_action('wp_footer', function () {
    $camping = 'orbitur';
    $lang = 'pt';
    echo <<<HTML
<script>
  (function(t,h,e,l,i,s,R,E,S,A){
      t[i]={},t[i][s]=t[i][s]||function(){
          if(1===arguments.length){return t[i].a[arguments[0]]||null}
          else{t[i].a=t[i].a||[]; t[i].a[arguments[0]] = arguments[1];}
      },
          R=h.createElement(e),E=h.getElementsByTagName(e)[0];
      R.async=1;R.src=l;E.parentNode.insertBefore(R,E)
  })(window,document,"script","https://thelisresa.webcamp.fr/ilib/v4/?categories&favorites&searchengine&simpleblock","thelisresa","ilib");
  thelisresa.ilib('camping', 'orbitur');
  thelisresa.ilib('language', 'pt');
</script>
HTML;
});