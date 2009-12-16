(define "PHAREN_PATH" "/pharen")
(define "REQUEST" (trim (substr (str_replace PHAREN_PATH "" ($ server "REQUEST_URI")) 1) "/"))
(define "BASE" (dirname __FILE__))

(fn hi [name]
    (echo "Hi " name "!<br/>"))

(fn route [req]
	(= parts (explode "/" req))
	(call_user_func_array (at parts 0) (array_slice parts 1)))

(route REQUEST)

(echo "
	<form action='' method='POST'>
		Name: <input type='text' name='name'/><br/>
		<input type='submit' name='submit' value='submit'/>
	</form>
")
