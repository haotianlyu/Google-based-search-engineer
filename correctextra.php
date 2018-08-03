<?php
//set memory_limit to unlimited
ini_set('memory_limit', '-1');
//set execution time to unlimited to make sure big.txt could be load successfully
ini_set('max_execution_time', 0);

header('Content-Type: text/html; charset = utf-8');

$limit = 10;

$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;

$results = false;



if($query) {

  require_once('solr-php-client/Apache/Solr/Service.php');

  require_once('SpellCorrector.php');

  require_once('simple_html_dom.php');

  $solr = new Apache_Solr_Service('localhost', 8983, 'solr/newcore');

  if(get_magic_quotes_gpc() == 1) {

        $query = stripslashes($query);

    }

    

    try {

        if(!isset($_GET['searchType']))$_GET['searchType']="solr";

        if($_GET['searchType'] == "solr") {

           $results = $solr->search($query,0,$limit);

        }else {

           $param = array('sort'=>'pageRankFile desc');

     $results = $solr->search($query, 0, $limit, $param);

        }

    }catch(Exception $e) {

      die("<html><head><title>SEARCH EXCEPTION</title></head><body><pre>{$e->__toString()}</pre></body></html>");

    }

}

?>



<html>

<link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">

  <script src="http://code.jquery.com/jquery-1.12.4.js"></script>

  <script src="http://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
//function about autocomplete, using jquery and jquery ui to show the ui part, in autocomplete function, using ajax to call suggest function to provide some sample query words
  $(function() {

    $("#q").autocomplete({

      source : function(request, response) {

        var lastquery = $("#q").val().toLowerCase().replace(/\s+/g," ").split(" ").pop(-1);

        var URL = "http://localhost:8983/solr/newcore/suggest?indent=on&q=" + lastquery + "&wt=json";

        $.ajax({

            url : URL,

            success : function(data) {                

                var lastquery = $("#q").val().toLowerCase().replace(/\s+/g," ").split(" ").pop(-1);

                var suggestions = data.suggest.suggest[lastquery].suggestions;

                suggestions = $.map(suggestions, function (value, index) {

                    var queryset = $("#q").val().toLowerCase().replace(/\s+/g," ").split(" ");

                    var prequery = "";

                    for(var i = 0;i < queryset.length - 1; i++) {

                        prequery = prequery+queryset[i]+" ";

                    }

                    if (!/^[a-zA-Z0-9]+$/.test(value.term)) {

                        return null;

                    }

                    return prequery + value.term;

                });



                console.log(suggestions);

                response(suggestions);

            },

            dataType : 'jsonp',

            jsonp : 'json.wrf'

        });

    },

    minLength : 1

});

});



</script>



  <head>

    <title> Solr </title>

  </head>

  <body>

   <form accept-charset="utf-8" method = "get">

    <label for="q">Search</label>

    <input id = "q" name = "q" type = "text" value = "<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8');?>"/>

    <input type="radio" name="searchType" value="solr"<?php if(!isset($_GET['searchType']) || $_GET['searchType'] == "solr") echo "checked";?>/> Solr Default Search

    <input type="radio" name="searchType" value="pagerank" <?php if(isset($_GET['searchType']) && $_GET['searchType'] == "pagerank") echo "checked";?>/> PageRank

    <input type="submit"/>

    </form>

<?php

  if($results) {

    $total = (int) $results->response->numFound;

    $start = min(1, $total);

    $end = min($limit, $total);

?>

<?php

  if ($total == "" || $total == 0):

     $query = str_replace("!\s+!"," ",$query);
 
     $uncorrect = explode(" ",$query);

     $correct = "";

     foreach($uncorrect as $single) {
       
        $correct = $correct.SpellCorrector::correct($single)." ";

     }

     if(strcmp($query, trim($correct)) != 0):

?>
    
     <p>Your input is incorrect, do you want to search: <a href="http://localhost/correctextra.php?q=<?php echo trim($correct);?>&searchType=<?php echo ($_GET['searchType']); ?>"><?php echo $correct; ?></a></p>

     <?php else: ?>
      
     <p>No result</p>

<?php 
 
      endif;

      endif;

?>

  <div>Results<?php echo $start; ?> - <?php echo $end;?> of <?php echo $total;?>:</div>

  <ol>

  <?php

     $csv=array();

     try{

       $file=fopen("/home/lyuhaoti/Desktop/WP Map.csv","r");

       while(!feof($file)) {

         $line=fgetcsv($file,1024);

         $csv[$line[0]]=$line[1];

       }

       fclose($file);

     } catch (Exception $err) {

       echo $err->getMessage();

     }

     $docs = $results->response->docs;

     foreach($docs as $doc){

       $link = "";

       $id = "";

       $id = str_replace("/home/lyuhaoti/solr-7.1.0/crawl_data/WP/","",$doc->id);

       $link = $csv[$id];

       $content = file_get_html($doc->id)->plaintext;

       $sentences = preg_split('/(\.)/', $content);     

       $snippet = " ";

       $term = trim($query); 

      $regex = "/\b".$term."/i";

      foreach($sentences as $sentence){ 

            if (preg_match($regex, $sentence)) {

                $index = stripos($sentence, $term);

                $startIndex = max(0, $index-100);
      
                $snippet = substr(trim($sentence),$startIndex, 160);

                //echo "find result in first section";
     
                break;
            }

          if($snippet != " "){
   
                break;
          
          }
    
       }  

       $letters = explode(" ", $query); 

       $size = count($letter);

       if($snippet == " ") {

          foreach($sentences as $sentence){ 
           
          $sign = true;

          $startIndex = 0;

          $endIndex = 2147483647;
  
          for($i = 0; $i < $size; $i++){
    
            $regex = "/\b".$letters[i]."\b/i";
    
            if (preg_match($regex, $sentence)) {

                $index = stripos($sentence, $letters[i]);

                $startIndex = min($startIndex, $index);

                $endIndex = max($endIndex, endIndex);
 
                $diff = max($endIndex - $startIndex, 160);

                $snippet = substr(trim($sentence),$startIndex, $diff);

            }

            else {
            
                $snippet = "";

                $sign = false;
       
                break;

            }

            if($sign == false) {
              
                break;
   
            }
        
          }

          if($snippet != " "){
   
                break;
          
          }
    
       }

       }

       if($snippet == " ") {

          foreach($sentences as $sentence){ 
  
          foreach($letters as $term){
    
            $regex = "/\b".$term."\b/i";
    
            if (preg_match($regex, $sentence)) {
      
                $index = stripos($sentence, $term);

                $startIndex = max(0, $index-60);
      
                $snippet = substr(trim($sentence),$startIndex, 160);

                break;

            }
        
          }

          if($snippet != " "){
   
                break;
          
          }
    
       }

       } 

       $snippet = "...".$snippet."...";

   ?>

   <li>

    <table style = "border: 1px solid black; width : 90%; text-align: left">

      <tr>

      <th width="7%">Title</th>

      <td width="93%"><?php echo "<a href = '{$link}' target='_blank'><b>".$doc->title."</b></a>" ?></td>

      </tr>

      <tr>

      <th>Link</th>

      <td><?php echo "<a href = '{$link}' target='_blank'><b>".$link."</b></a>" ?></td>

      </tr>

      <tr>

      <th>Id</th>

      <td><?php echo htmlspecialchars($doc->id, ENT_NOQUOTES, 'utf-8'); ?></td>

      </tr>

      <tr>

      <th>Snippet</th>

      <td><?php echo $snippet; ?></td>

      </tr>

    </table>

    </li>

    <?php

      }

    ?>

    </ol>

<?php 

 }

?>

</body>

</html>
