$(document).ready(function(){   
    $("#loadstudent").on("click", function(event){  
       $.ajax({  
          url:        '/prostam',  
          type:       'POST',   
          dataType:   'json',  
          async:      true,  
          success: 
            function(data, status) {  
                  var e = $('<tr><th>Name</th><th>Address</th></tr>');  
                  $('#student').html('');  
                  $('#student').append(e);  
                  
                  for(i = 0; i < data.length; i++) {  
                     student = data[i];  
                     var e = $('<tr><td id = "name"></td><td id = "address"></td></tr>');
                     
                     $('#name', e).html(student['title']);  
                     $('#address', e).html(student['content']);  
                     $('#student').append(e);  
                  }  
          },  
          error : function(xhr, textStatus, errorThrown) {  
             alert('Ajax request failed.');  
          }  
       });  
    });  
 });  

