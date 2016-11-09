<html>
  <head>
      <title>
        Post, Put and Delete Records
      </title>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
      <script>
      $(document).ready(function() {

        $('input[type=radio][name=method]').click(function(){
        var page=$(this).val();
        if(page == "currPost.php" || page == "currPut.php") {
          $('input[name=code]').prop('disabled',false);
          $('input[name=cname]').prop('disabled',false);
          $('input[name=rate]').prop('disabled',false);
          $('input[name=countries]').prop('disabled',false);
        } else {
          $('input[name=code]').prop('disabled',false);
          $('input[name=cname]').prop('disabled',true);
          $('input[name=rate]').prop('disabled',true);
          $('input[name=countries]').prop('disabled',true);
        }

        });

          $('#requestForm').on('submit', function(e) {
            e.preventDefault();
            var page = $('input[name=method]:checked').val();

            $.ajax({
              type: 'POST',
              url: page,
              data: $('#requestForm').serialize(),
              success: function(data){
                $('#response').val(data);
              },
              error:function(data){
                $('#response').val("server no response");
              },
              beforeSend: function(e) {
                $('#submitBtn').prop("disabled",true);
              },
              complete: function(e) {
                $('#submitBtn').prop("disabled",false);
              }
            });
        });
      });
      </script>
  </head>
  <style>
    div.form {
      width:30%;
      border: 1px solid #4d4d4d;
      border-radius: 20px;
      padding: 20px;
    }
    input[type=text],input[type=number] {
      padding: 12px 20px;
      line-height: 0.8;
      border-style: solid;
      border-color: #d9d9d9;
      border-radius: 20px;
    }
    input[type=text]:focus, input[type=number]:focus {
      background-color: #d9d9d9;
      color: #000000;
    }
    input[type=submit] {
      font-size:13px;
      padding:10px;
      background-color: #4CAF50;
      color:white;
      border-radius:5px;
      border: none;
    }
    input[type=submit]:active {
      background-color: #367c39;
    }
    div.response {
      width:30%;
    }
  </style>
  <body>
    <div class="form">
      <form action="ppd.php" method="post" id="requestForm">
        Form interfce for POST, PUT &amp; DELETE<br>
         <input  type="radio" name="method" value="currPost.php" checked="checked"/>Post
         <input type="radio" name="method" value="currPut.php"/>Put
         <input type="radio" name="method" value="currDel.php"/>Delete<br>
         Currency Code<br>
         <input type="text" name="code" placeholder="code" style="width: 30%"/><br>
         Currency Name<br>
         <input type="text" name='cname' placeholder="name" style="width: 40%"/><br>
         Rate(£=1)<br>
         <input type="number"  name='rate' step="0.0001" placeholder="rate" style="width: 25%"/><br>
         Countries(Comma separated if 1+)<br>
         <input type="text"   name='countries' placeholder="countries" style="width: 80%"/><br><br>
         <input type="submit" id="submitBtn"/>
      </form>
    </div>
    <br>
      <big>Click <a href="rates.xml" target="_blank">here</a> to check the file.</big>
      <br>
      <div class="response">
      Response Message:<br>
      <textarea id="response" rows="20" cols="80" placeholder="Response XML"></textarea>
      </div>



  </body>
</html>
