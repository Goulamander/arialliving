/*
|--------------------------------------------------------------------------
| Global helpers
|--------------------------------------------------------------------------
|
*/




// Create initial from Name

function nameInitials(name, id = null, is_thumb = false, is_label = false) {

    if(!name) return '...';

    let words = name.split(" "),
    initials = ''

    for ( var i = 0, l = words.length; i < l; i++ ) {
        if(i==2) break;
        initials += words[i][0]
    }

	if(is_thumb) {
        return `<img class="profile-thumb" src="/storage/a/users/${id}.jpg" alt="${name}" ${is_label ? `data-tippy-content="${name}"` : ``}/>`;
	}
    return `<span class="initials" ${is_label ? `data-tippy-content="${name}"` : ``}>${initials.toUpperCase()}</span>`;
}



//
function convertToID(id, prefix) {
    return prefix + '-' + __pad(id, 4)
}



//
function __pad(id, size) {
    var s = id + "";
    while (s.length < size) s = "0" + s;
    return s;
}



// to format currency
// number:= number, float, decimal, int
// return string
function NumberFormat(number) {
    // EXAMPLE --------------
    // 5000.035 -> 5,000.04
    // 5000.034 -> 5,000.03
    // EXAMPLE --------------
    return new Intl.NumberFormat("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2  }).format(number)
}



// to parse currency to number
// number:= any
// return float
function StrToNumber(number) {
    number = number.toString()
    return parseFloat(number.replace(/[^0-9-.]/g, ''))
}



// to calculate percentage
// percent:= float
// number:= float
// return float
function CalculatePercentage(percent ,number) {
    return (percent / 100) * number
}




// Validate Email address
function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email)
}



/**
 * Capitalize
 * @param {String} s
 */
const capitalize = (s) => {
    if (typeof s !== 'string') return ''
    return s.charAt(0).toUpperCase() + s.slice(1)
}



/**
 * Convert Slug to Str
 * 
 * @param {String} slug
 */
String.prototype.slugToStr = function() {
    a = this.replace('-', ' ')
    return a.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase() })
};




function textTruncate(fullStr, strLen, separator) {

    if (fullStr.length <= strLen) return fullStr

    separator = separator || '...'

    var sepLen = separator.length,
        charsToShow = strLen - sepLen,
        frontChars = Math.ceil(charsToShow/2),
        backChars = Math.floor(charsToShow/2)

    return fullStr.substr(0, frontChars) + 
           separator + 
           fullStr.substr(fullStr.length - backChars)

}


/**
 * base64 url encoding
 * @param {String} str
 */
function base64url_encode(str) {

  // First of all you should encode $data to Base64 string
  let b64 = btoa(str)
  // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
  if (b64 === false) {
    return false
  }
  // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
  let url = b64.replace('+/', '-_')

  // Remove padding character from the end of line and return the Base64URL result
  return url.replace(/=+$/,'')
}



function round(value, step) {
    step || (step = 1.0);
    var inv = 1.0 / step;
    return Math.round(value * inv) / inv;
}


/**
 * Trim all duplicate white spaces
 * 
 */
String.prototype.allTrim = String.prototype.allTrim ||
     function() {
        return this.replace(/\s+/g,' ')
                   .replace(/^\s+|\s+$/,'');
     };



/** 
 * Set, Get Cookies
*/
function setCookie(cookieName, cookieValue, daysToExpire) {

    const date = new Date()

    date.setTime(date.getTime()+(daysToExpire*24*60*60*1000))
    document.cookie = cookieName + "=" + cookieValue + "; expires=" + date.toGMTString()
}

function getCookie(cookieName) {

    let name = cookieName + "="

    let allCookieArray = document.cookie.split(';')

    for(var i=0; i<allCookieArray.length; i++) {

        let temp = allCookieArray[i].trim()
        if (temp.indexOf(name)==0)
        return temp.substring(name.length,temp.length)
    }

    return ""
}




/**
 * Add an element to DOM 
 * 
 * @param {String} el
 */
injectElement = function(target, el) {

    if(!el) return

    let newDiv = $.parseHTML(el.allTrim())[0],
        height = getNodeHeight(newDiv)
        newDiv.style.setProperty('--h', height+ "px")
        newDiv.classList.add('add_in')
      
        target.prepend(newDiv)
        return
}

/**
 * Get the hight of a node that is not yet added to DOM
 * 
 * @param {node} node 
 */
function getNodeHeight(node) {

    var height, 
        clone = node.cloneNode(true)

    clone.style.cssText = "position:fixed; top:-9999px; opacity:0;"
    document.querySelector('.cart-items').appendChild(clone)
    height = clone.clientHeight
    clone.parentNode.removeChild(clone)
    return height
}


/**
 * Remove an element form DOM 
 * 
 * @param {*} el
 */
removeElement = function(el) {
    
    if(!el) return

    el.classList.add('remove_item')

    setTimeout(() => {
        el.remove()
    }, 700)
}


/**
 * Remove all contents in a modal
 * @param {el} modal
 */
cleanModal = function(modal) {
    modal.querySelector('.modal-body').innerHTML = ""
    //modal.querySelector('.modal-header h3').innerHTML = ""
}


/**
 * Clear a form
 * 
 */
$.fn.clearForm = function() {

    return this.each(function() {
      var type = this.type, tag = this.tagName.toLowerCase();
      if (tag == 'form')
        return $(':input',this).clearForm();
      if (type.includes('text', 'email', 'number', 'password', 'date') || tag == 'textarea')
        this.value = '';
      else if (type == 'checkbox' || type == 'radio')
        this.checked = false;
      else if (tag == 'select')
        this.selectedIndex = 0; // select first value
    })

}


function removeArrayItem(arr, value) {
    var index = arr.indexOf(value);
    if (index > -1) {
      arr.splice(index, 1);
    }
    return arr;
}


/**
 * Handle axios error response
 * 
 */
_errorResponse = function(error) {

    console.log(error)

    if(typeof error.response.status == 'undefined') {
        return 
    }

    // Form Validation Errors
    switch(error.response.status) {
        
        // data validation issues
        case 422:
            $response_body = Object.values(error.response.data.errors).map(function(err) {
                return err
            }).join(', ')

            break;
        
        // Controller Error response
        case 400:
            $response_body = error.response.data.error
            break;

        // Something wet wrong.
        default:
            let msg = `
                <b>Error:</b> ${error.response.data.message}<br>
                <b>File:</b> ${error.response.data.file}<br>
                <b>Line:</b> ${error.response.data.line}<br>`

            //$response_body = JSON.stringify(error.response.message);
            $response_body = msg;
            break;
    }
    // Flash the message
    sc.alert.show('alert-danger', $response_body, 5000) 
    return
}


/**
 * Color blending
 * 
 * @param {*} p 
 * @param {*} c0 
 * @param {*} c1 
 * @param {*} l 
 */
const pSBC = (p,c0,c1,l) => {
    let r,g,b,P,f,t,h,i=parseInt,m=Math.round,a=typeof(c1)=="string";
    if(typeof(p)!="number"||p<-1||p>1||typeof(c0)!="string"||(c0[0]!='r'&&c0[0]!='#')||(c1&&!a))return null;
    if(!this.pSBCr)this.pSBCr=(d)=>{
        let n=d.length,x={};
        if(n>9){
            [r,g,b,a]=d=d.split(","),n=d.length;
            if(n<3||n>4)return null;
            x.r=i(r[3]=="a"?r.slice(5):r.slice(4)),x.g=i(g),x.b=i(b),x.a=a?parseFloat(a):-1
        }else{
            if(n==8||n==6||n<4)return null;
            if(n<6)d="#"+d[1]+d[1]+d[2]+d[2]+d[3]+d[3]+(n>4?d[4]+d[4]:"");
            d=i(d.slice(1),16);
            if(n==9||n==5)x.r=d>>24&255,x.g=d>>16&255,x.b=d>>8&255,x.a=m((d&255)/0.255)/1000;
            else x.r=d>>16,x.g=d>>8&255,x.b=d&255,x.a=-1
        }return x};
    h=c0.length>9,h=a?c1.length>9?true:c1=="c"?!h:false:h,f=this.pSBCr(c0),P=p<0,t=c1&&c1!="c"?this.pSBCr(c1):P?{r:0,g:0,b:0,a:-1}:{r:255,g:255,b:255,a:-1},p=P?p*-1:p,P=1-p;
    if(!f||!t)return null;
    if(l)r=m(P*f.r+p*t.r),g=m(P*f.g+p*t.g),b=m(P*f.b+p*t.b);
    else r=m((P*f.r**2+p*t.r**2)**0.5),g=m((P*f.g**2+p*t.g**2)**0.5),b=m((P*f.b**2+p*t.b**2)**0.5);
    a=f.a,t=t.a,f=a>=0||t>=0,a=f?a<0?t:t<0?a:a*P+t*p:0;
    if(h)return"rgb"+(f?"a(":"(")+r+","+g+","+b+(f?","+m(a*1000)/1000:"")+")";
    else return"#"+(4294967296+r*16777216+g*65536+b*256+(f?m(a*255):0)).toString(16).slice(1,f?undefined:-2)
}



/**
 * 
 */
getCleanPDFNameFromPath = function(url) {

    let filename = url.substring(url.lastIndexOf('/')+1) // get the name
        filename = filename.replaceAll('-', ' ') // replace - to spaces
        filename = filename.replace('.pdf', '') // remove extension
        filename = filename.replace(/^(.*?)___/, ' ') // remove the Order info

        filename = filename.toLowerCase().replace(/\b[a-z]/g, function(letter) {
            return letter.toUpperCase();
        })

        return filename.trim()
}