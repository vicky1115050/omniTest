<!DOCTYPE html>
<html>
<head>
<style>
#customers {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
}

#customers td, #customers th {
    border: 1px solid #ddd;
    padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
</style>
</head>
<body>

<div>
  <h1>Calender Events</h1>
  <a href="/logout" style="float: right;font-style: 15px;">sign out</a>
</div>
<table id="customers">
  <tr>
    <th>Title</th>
    <th>Created</th>
    <th>Owner</th>
  </tr>
  <?php foreach($events as $event){

            $eve = json_decode($event['event_json']);

            echo "<tr><td>".$eve->summary."</td><td>".$eve->created."</td><td>".$eve->creator->displayName."</td></tr> ";

            }
  ?>
</table>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
    
        var worker = function(){
          $.get("/events", function(data, status){
            console.log(data);
          });
        };

         var watcher = function(){
          $.get("/watch", function(data, status){
            console.log(data);
            var response = JSON.parse(data);
            if(response.SUCCESS == 1){
              alert(response.DATA);
            }
          });
        };

        setInterval(worker, 3000);

        setInterval(watcher, 3000);
});
</script>

</body>
</html>
