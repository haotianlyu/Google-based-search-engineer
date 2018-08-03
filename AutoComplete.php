<?php
ini_set('memory_limit', '1024M');
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
?>
<script
  src="http://code.jquery.com/jquery-1.12.4.js"
  integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU="
  crossorigin="anonymous"></script>
<script
  src="http://code.jquery.com/ui/1.12.1/jquery-ui.js"
  integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30="
  crossorigin="anonymous"></script>
<script>
  $(function() {
    $("#q").autocomplete({
      source : function(request, response) {
        var value = encodeURI($('#q').value().toLowerCase());
        $.ajax({
            url : "http://localhost:8983/solr/newcore/suggest?q="+value+"&wt=json",
            success : function(data) {
                var suggestions = data.suggest.suggest.suggestions;
                suggestions = $.map(suggestions, function (value, index) {
                    return value.term;
                });
                response(suggestions.slice(0, 5));
            },
            dataType : 'jsonp',
            jsonp : 'json.wrf'
        });
    },
    minLength : 1
});
});
    </script>
<html>
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
</body>
</html>