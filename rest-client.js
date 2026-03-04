$.ajax({ 
    type: 'GET',
    dataType: 'json',
    url: 'http://localhost/projeto-php/rest.php', 
    data: { 
        'endpoint': 'pessoas.show',
        'id'    : '1'
    }, 
    success: function (response) { 
        console.log(response.data);
    }
});
