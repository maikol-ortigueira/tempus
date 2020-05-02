function getProfileValues(userId, values='') {
	var fields = ['profile_key','profile_value'];
	var table = 'user.profiles';
	var wherefield = 'user_id';
	var wherevalue = userId;
	var condition = '=';

	var request = getValues(fields, table, wherefield, wherevalue, condition).done(function(result) {
		var profile = new Object();
		result.forEach(element => profile[element.profile_key] = element.profile_value);
		console.log(profile);
		return profile;
	});
	return request;
}

/**
 * Método para recuperar valores de una tabla vía ajax
 * @param  {array} fields     Array con los campos a recuperar
 * @param  {string} table      nombre de la tabla sustituyendo _ por un punto
 * @param  {string} where      Campo condicional
 * @param  {string} wherevalue Valor que deberá tener el campo condicional
 * @param  {string} condition  tipo comparativo
 * @return {json}            Valores de los campos solicitados.
 */
function getValues(fields, table, wherefield, wherevalue,condition='=') {
	var getUrl    = "index.php?option=com_tempus&task=ajax.getValues&format=json";

	if (token.length > 0) {
		var request = 'token='+token+'&wherevalue='+wherevalue+'&fields='+fields+'&table='+table+'&wherefield='+wherefield+'&condition='+condition;
	}

	return jQuery.ajax({
		url: getUrl,
		type: 'GET',
		dataType: 'jsonp',
		data: request,
		jsonp: 'callback'
	});
}