<!-- Hiding JavaScript

// JavaScript Document

//
// Each required form field can be checked with JavaScript. Here are 
//    the function names for the different kinds of checks:
//
//       1. WithoutContent() -- check if the text, textarea, password, 
//              or file fields has no content.
//       2. NoneWithContent() -- check if none of the set of text, 
//              textarea, password, or file fields have content. 
//              (Set: More than one with the same field name.)
//
//       3. NoneWithCheck() -- check if none of the set of radio buttons 
//              or checkboxes are checked. (Set: More than one with the 
//              same field name.)
//       4. WithoutCheck() -- check if the single radio button or checkbox 
//              is unchecked.
//
//       5. WithoutSelectionValue() -- check if selected drop-down list or 
//              select box entries have no value.
//
//       6. NoneWithCheckArray() -- check if none of the set of checkboxes 
//				are checked in array. (Set: More than one with the 
//              same field name.)
//
// The format for using the above functions is
//             if(       WithoutContent([FORMFIELDVALUE])) [ERRORMESSAGE]
//             if(      NoneWithContent([FORMFIELD])     ) [ERRORMESSAGE]
//             if(        NoneWithCheck([FORMFIELD])     ) [ERRORMESSAGE]
//             if(         WithoutCheck([FORMFIELD])     ) [ERRORMESSAGE]
//             if(WithoutSelectionValue([FORMFIELD])     ) [ERRORMESSAGE]
//
// The if(...) part and the error message part may be on separate lines, like
//             if(WithoutContent([FORMFIELDVALUE]))
//                [ERRORMESSAGE]
//             if(NoneWithContent([FORMFIELD]))
//                [ERRORMESSAGE]
//             if(NoneWithCheck([FORMFIELD]))
//                [ERRORMESSAGE]
//             if(WithoutCheck([FORMFIELD]))
//                [ERRORMESSAGE]
//             if(WithoutSelectionValue([FORMFIELD]))
//                [ERRORMESSAGE]
//
//
//      FORMFIELD -- The format for specifying a "form field" is 
//                         document.[FORMNAME].[FIELDNAME]
// FORMFIELDVALUE -- The format for specifying a "form field value" is 
//                         document.[FORMNAME].[FIELDNAME].value
//   ERRORMESSAGE -- The format for specifying an "error message" is
//                         { errormessage += "\n\n[MESSAGE]"; }
//                   If the message itself contains quotation marks, 
//                      they must be preceded with a back slash. 
//                      Example: \"
//
//
//      FORMNAME -- The name assigned to the form in the <FORM... tag. 
//     FIELDNAME -- The field name being checked.
// 
//
// For use with this JavaScript, the only non-alphanumeric character a 
//    fieldname may have is the underscore. Replace any hyphens, colons, 
//    spaces, or other non-alphanumeric characters in your field names 
//    with an underscore character.
//
//
// Put field checks into the function CheckRequiredFields(), in the order 
//    you want the fields checked.
//

	function WithoutContent(ss) {
		if(ss.length > 0) { return false; }
		return true;
	}
	
	function NoneWithContent(ss) {
		for(var i = 0; i < ss.length; i++) {
			if(ss[i].value.length > 0) { return false; }
			}
		return true;
	}
	
	function NoneWithCheck(ss) {
		for(var i = 0; i < ss.length; i++) {
			if(ss[i].checked) { return false; }
			}
		return true;
	}
	
	function WithoutCheck(ss) {
		if(ss.checked) { return false; }
		return true;
	}
	
	function WithoutSelectionValue(ss) {
		for(var i = 0; i < ss.length; i++) {
			if(ss[i].selected) {
				if(ss[i].value.length) { return false; }
				}
			}
		return true;
	}
	
	function NoneWithCheckArray(ss) {

		var elements = new Array();
		var elements = ss;
		var elementschecked = 0;
		for(i=0;i<elements.length;i++){
			if(elements[i].checked){
				elementschecked = elementschecked + 1;
			}
		}
		if (elementschecked==0){
			return true;
		} else {
			return false;
		}
		
		return true;
	} 
	

//-----


// FUNCTION
// Para aceptar solo n˙meros en el textfield
	function CheckNumbersOnly(myfield, e, dec) {
		var key;
		var keychar;
		
		if (window.event)
		   key = window.event.keyCode;
		else if (e)
		   key = e.which;
		else
		   return true;
		keychar = String.fromCharCode(key);
		
		// control keys
		if ((key==null) || (key==0) || (key==8) || 
			(key==9) || (key==13) || (key==27) )
		   return true;
		
		// numbers
		else if ((("0123456789").indexOf(keychar) > -1))
		   return true;
		
		// decimal point jump
		else if (dec && (keychar == "."))
		   {
		   myfield.form.elements[dec].focus();
		   return false;
		   }
		else
		   return false;
	}


// FUNCTION
// Para aceptar solo los caracteres que se envien en la funciÛn
	var loginchars = 'ABCDEFGHIJKLMN—OPQRSTUVWXYZabcdefghijklmnÒopqrstuvwxyz1234567890*!#$._'
	var letters = ' ABCDEFGHIJKLMN—OPQRSTUVWXYZabcdefghijklmnÒopqrstuvwxyz·¡È…ÌÕÛ”˙⁄'
	var numbers = '1234567890'
	var signs = ',.:;@-\''
	var mathsigns = '+-=()*/'
	var custom = '<>#$%&?ø'
	
	function CheckCharactersOnly(e,allow) {
		var k;
		k=document.all?parseInt(e.keyCode): parseInt(e.which);
		return (allow.indexOf(String.fromCharCode(k))!=-1);
	}
	

// FUNCTION
// Para validar que la fecha enviada sea v·lida
	function IsValidDate(fecha){
		var validformat=/^\d{2}\/\d{2}\/\d{4}$/ //Basic check for format validity
		var returnval=false
		if (!validformat.test(fecha))
			returnval=false
		else{ //Detailed check for valid date ranges
		var monthfield=fecha.split("/")[0]
		var dayfield=fecha.split("/")[1]
		var yearfield=fecha.split("/")[2]
		var dayobj = new Date(yearfield, monthfield-1, dayfield)
		if ((dayobj.getMonth()+1!=monthfield)||(dayobj.getDate()!=dayfield)||(dayobj.getFullYear()!=yearfield))
			returnval=false
		else
			returnval=true
		}
		return returnval
	}


// FUNCTION
// Para validar que el email sea v·lido
	function IsValidEmail(strEmail){
	   var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	   var address = strEmail;
	   if(reg.test(address) == false) {
		  return false;
	   }
	   return true;	
	}	
	

// FUNCTION
// Para desplazarse al siguiente campo en automatico
	function GoToNextField(origen, longitud, destino) {
		var letters = origen.value.length +1;
		if (letters <= longitud) {
			origen.focus();
		} else {
			//document.frmafiliacion.mes.focus();
			document.forms[0].elements[destino].focus();
		}
	}
	
	
// FUNCTION
// Para validar passwords o usernames
	/*
		Password Validator 0.1
		(c) 2007 Steven Levithan <stevenlevithan.com>
		MIT License
	*/
	function validatePassword (pw, options) {
		// default options (allows any password)
		var o = {
			lower:    0,
			upper:    0,
			alpha:    0, /* lower + upper */
			numeric:  0,
			special:  0,
			length:   [0, Infinity],
			custom:   [ /* regexes and/or functions */ ],
			badWords: [],
			badSequenceLength: 0,
			noQwertySequences: false,
			noSequential:      false
		};
	
		for (var property in options)
			o[property] = options[property];
	
		var	re = {
				lower:   /[a-z]/g,
				upper:   /[A-Z]/g,
				alpha:   /[A-Z]/gi,
				numeric: /[0-9]/g,
				special: /[\W_]/g
			},
			rule, i;
	
		// enforce min/max length
		if (pw.length < o.length[0] || pw.length > o.length[1])
			return false;
	
		// enforce lower/upper/alpha/numeric/special rules
		for (rule in re) {
			if ((pw.match(re[rule]) || []).length < o[rule])
				return false;
		}
	
		// enforce word ban (case insensitive)
		for (i = 0; i < o.badWords.length; i++) {
			if (pw.toLowerCase().indexOf(o.badWords[i].toLowerCase()) > -1)
				return false;
		}
	
		// enforce the no sequential, identical characters rule
		if (o.noSequential && /([\S\s])\1/.test(pw))
			return false;
			
		//accentedcharacters = new RegExp(/^[·ÈÌÛ˙]+$/);    
		//if (accentedcharacters.test(pw))
		//	return false;

		// enforce alphanumeric/qwerty sequence ban rules
		if (o.badSequenceLength) {
			var	lower   = "abcdefghijklmnopqrstuvwxyz",
				upper   = lower.toUpperCase(),
				numbers = "0123456789",
				qwerty  = "qwertyuiopasdfghjklzxcvbnm",
				start   = o.badSequenceLength - 1,
				seq     = "_" + pw.slice(0, start);
			for (i = start; i < pw.length; i++) {
				seq = seq.slice(1) + pw.charAt(i);
				if (
					lower.indexOf(seq)   > -1 ||
					upper.indexOf(seq)   > -1 ||
					numbers.indexOf(seq) > -1 ||
					(o.noQwertySequences && qwerty.indexOf(seq) > -1)
				) {
					return false;
				}
			}
		}
	
		// enforce custom regex/function rules
		for (i = 0; i < o.custom.length; i++) {
			rule = o.custom[i];
			if (rule instanceof RegExp) {
				if (!rule.test(pw))
					return false;
			} else if (rule instanceof Function) {
				if (!rule(pw))
					return false;
			}
		}
	
		// great success!
		return true;
	}	
	
	
// FUNCTION
// Para validar un URL
	// http://stackoverflow.com/questions/1303872/trying-to-validate-url-using-javascript
	function isInvalidURL(url){
		var RegExp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
	
		if(RegExp.test(url)){
			return false;
		}else{
			return true;
		}
	} 	

// FUNCTION
// Para validar una IP
		function verifyIP (IPvalue) {
			errorString = "";
			theName = "IPaddress";
			
			var ipPattern = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
			var ipArray = IPvalue.match(ipPattern);
			
			if (ipArray == null)
				errorString = errorString + theName + ': '+IPvalue+' is not a valid IP address.';
			else {
					for (i = 1; i < 5; i++) {
						thisSegment = ipArray[i];
						if (thisSegment > 255) {
							errorString = errorString + theName + ': '+IPvalue+' is not a valid IP address.';
							i = 5;
						}
						if ((i == 0) && (thisSegment > 255)) {
							errorString = errorString + theName + ': '+IPvalue+' is a special IP address and cannot be used here.';
							i = 5;
						}
				   }
			}
			extensionLength = 3;
			if (errorString == "")
				alert ("That is a valid IP address.");
			else
				alert (errorString);
		}	
	
// End hiding JavaScript -->