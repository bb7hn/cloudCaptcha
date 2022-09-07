# Cloud Captcha
## Presentation
I created cloud captcha as a solution for my api. My api end point is not secret so with some way I need to create a captcha somehow and validate it too while registering a user. So I tried to create a captcha creator and validator as micro service.
<br/>
## How It Works ?
### Capctha Creation :
When user request to index with create method (default method is create) Cloud captcha creates captcha images with custom font by using PHP's imagegd and saves them into captcha folder. Then Cloud Captcha creates a json file into validate folder.
### Capctha Validation :
No one/nothing (except validate script) can not access validate folder because .htaccess file denies request for this folder.
### Does it require too many disk space?
It depends to request count. For to solve this problem I created ***deleteOldFiles()*** Function. This function deletes old validation files and captcha images which created older than 15 minutes as default. If needed you can change just giving hour paramater to function. Hour parameter can be float for example for 1 minute parameter must be 1/60.
#### How to change captcha expire time ?
Open /config.php and find ***deleteOldFiles()*** Functions on lines 8 and 9 set the firs parameter which is hour parameter. Or alternatively you can change default value of function on line 22. Another alternative is: You can replace **$hour** variable on line 37.
# How To Use ?
Usage is pretty simple **for getting captcha image url**, just request to index. Index will return a json response.
Here is an example response:
```json
{
  "code": "200",
  "message": "Captcha created successfully and will expire in 15 minutes.",
  "id": "fb977c44-e692-4be8-ac1c-ccc08af27464",
  "src": "http://localhost:8000/captcha/fb977c44-e692-4be8-ac1c-ccc08af27464.png"
}
```
<br/>
For validation request to index by setting method parameter to validate, id to your captcha id and user's answer to captcha parameter. For make it clean here is an example request for validation:

    http://yourSite.com/servicePath/index.php?method=validate&id=captchaId&captcha=usersAnswer
<br/>

Example reponses:
```json
{
  "code": "406",
  "message": "Missing info: Captcha."
}
```
```json
{
  "code": "406",
  "message": "Missing info: Id."
}
```
```json
{
  "code": "406",
  "message": "Invalid info: Captcha."
}
```
```json
{
  "code": "406",
  "message": "Invalid info: Id."
}
```
```json
{
  "code": "401",
  "message": "Captcha is wrong."
}
```
```json
{
  "code": "200",
  "message": "Captcha is valid."
}
```
## Customization
Captcha background will be transparent everytime but you can set captcha text color by sending r,g and b parameters or theme parameter
Examples :
- RGB usage
```html
http://yourSite.com/servicePath/?r=100&g=100&b=100
```
- Theme usage

<br/>

    Dark (White Text)
    ```html
    http://yourSite.com/servicePath/?theme=dark
    ```
    Light (Black Text)
    ```html
    http://yourSite.com/servicePath/?theme=light
    ```
# Example Usage For PHP Server Side
```php
<?php
/*
    Some controls bla bla
*/
//this value comes from hidden input or sth else
$captchaId = $_POST['captchaId'];
//this value comes from form or sth else
$captcha = $_POST['captcha'];
$url = "http://yourSite.com/captchaService/?method=validate&captcha=$captcha&id=$captchaId";

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);

curl_close($curl);
$reponse = json_encode($response);

if(empty($response)){
    //validation failed 
    //there is a problem with curl or server
    //do sth
}
if($response->code != 406){
    //validation failed 
    //there is a problem with curl request
    //do sth
}
if($response->code != 401){
    //validation failed 
    //user input is invalid
    //do sth
}

?>
```