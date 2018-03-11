$(document).ready(function(){

    $("#application").validate({
        
       rules:{ 
        
            name:{
                required: true,
                minlength: 4,
                maxlength: 50,
            },
            
            email:{
                required: true,
                email: true
            },

        
       },
       
       messages:{
        
            name:{
                required: "Это поле обязательно для заполнения",
                minlength: "Логин должен быть минимум 4 символа",
                maxlength: "Максимальное число символо - 50",
            },
            
            email:{
                required: "Это поле обязательно для заполнения",
                email: "Пожалуйста, введите адрес электронной почты",
            },     
        
       }
        
    });



}); //end of ready