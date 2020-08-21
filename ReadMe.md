# rkw_ajax
## What does it do?
It adds AJAX-functionality to your extensions in an easy and convenient way.
There is no need to take care of AJAX request by yourself. Just code your extension and templates as usually – rkw_ajax will take care of the rest

## Setup
* Install the extension via composer
* Activate the extension in the backend
* The TypoScript-Settings of rkw_ajax will be activated automatically
* Now you need to activate the AJAX-Js via TypoScipt
```
plugin.tx_rkwajax {
    settings {

        # cat=plugin.tx_rkwbasics//a; type=boolean; label=Include JS for AJAX-API (Version 2)?
        includeAjaxApiJs2 = 1
    }
}
```
* Don't forget to inherit you regular `page`-object to the `txRkwAjaxPage`-object. This should happen after all other settings are done, so that both objects are identical. 
```
txRkwAjaxPage.10 < page.10
```
* You can test your setup by calling your website in a browser with `?type=250` appended to the URL. This should render your website with all contents, but without CSS or JavaScripts.
```
http://rkw-kompetenzzentrum.rkw.local/?type=250
```

## Usage
### Basics
First you will have to extend your `ActionContoller` from the `AjaxControllerAbstract`. This way the AJAX- functionality is added to you extension.
 ```
 class YourController extends \RKW\RkwAjax\Controller\AjaxAbstractController
 {
    [...]
 }
 ```
Now that the basic functionality is available we can use it. 
This is best explained using an example.
### Example
Let's pretend you have an extension which
 * renders a list of elements
 * a filter for this list
 * a more button for pagination
 
The Fluid-code may look like this:
```
<div class="filters">
    <select name="filter1">
        <option value="1">Value 1</option>
        <option value="2">Value 2</option>
    </select>
</div>
<div class="list">
    <div class="list__inner">
        <div class="list__item">
            Item 1
        </div>
        <div class="list__item">
            Item 3
        </div>
        <div class="list__item">
            Item 3
        </div>
    </div>
    <div class="button">
        <a href="#">More</a>
    </div>    
</div>            
```

So we probably want to archive the following behavior
* If the user filters the items of the list, the whole list should be refreshed via AJAX.
* If the user clicks the more button, further items should be added to the list
* In both cased the more button should be updated in order to have the correct page number as param

In order to archive this behavior we have to use the AjaxWrapper-ViewHelper. This ViewHelper marks the sections in your HTML that are to be used for Ajax. With it you can also define which action should take place.
The AjaxWrapper-ViewHelper expects the following params
* ajaxId: This is the internal ID you define in order to distinguish the sections you use from each other. You don't have to take care for namespaces. Just make sure each ID is numeric and is used only once across all your templates, partials and layouts.
* ajaxAction: Here you define what will happen with the code inside the ViewHelper-Tag when loaded via Ajax. If you set the value to "replace" it  will replace existing content, if you set the value to "append" it will be added at the end of the existing content, and if you set the value to "prepend" it will be added before the existing content. 
* ajaxHelper: Well, here you simply set the AjaxHelper-Object. 

First, lets take a look at the changed code before we try to explain why we did it this way.

```
<div class="filters">
    <form>
        <select name="filter1">
            <option value="1">Value 1</option>
            <option value="2">Value 2</option>
        </select>
        <button type="submit">Send</button>
    <form>    
</div>
<rkwAjax:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="replace" ajaxId="1">
    <div class="list">
        <rkwAjax:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="append" ajaxId="2">
            <div class="list__inner">
                <div class="list__item">
                    Item 1
                </div>
                <div class="list__item">
                    Item 3
                </div>
                <div class="list__item">
                    Item 3
                </div>
            </div>
        </rkwAjax:ajaxWrapper>

        <rkwAjax:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="replace" ajaxId="3">
            <div class="button">
                <a href="#">More</a>
            </div>  
        </rkwAjax:ajaxWrapper> 
    </div> 
</rkwAjax:ajaxWrapper>           
```
 
This will be code rendered to the frontend:
``` 
<div class="filters">
    <form>
        <select name="filter1">
            <option value="1">Value 1</option>
            <option value="2">Value 2</option>
        </select>
        <button type="submit">Send</button>
    <form>    
</div>
<div class="list" id="773bc02ea02b903280d609bb6a883735afbd7f14-1" data-rkwajax-id="1" data-rkwajax-action="replace">
    <div class="list__inner" id="773bc02ea02b903280d609bb6a883735afbd7f14-2" data-rkwajax-id="2" data-rkwajax-action="append">
        <div class="list__item">
            Item 1
        </div>
        <div class="list__item">
            Item 3
        </div>
        <div class="list__item">
            Item 3
        </div>
    </div>
    <div class="button" id="773bc02ea02b903280d609bb6a883735afbd7f14-3" data-rkwajax-id="3" data-rkwajax-action="replace">
        <a href="#">More</a>
    </div>    
</div>    
 ```
 As you can see the AjaxWrapper-ViewHelper will add some attributes to the first child element. Note that these attributes will only be added to defined set of valid tags (e. g. DIVs and FORMs).
 What did we do? We told the extension to:
 * Completely refresh the innerHTML of the DIV with the id "773bc02ea02b903280d609bb6a883735afbd7f14-1" - **BECAUSE**: If the user filters the items of the list, the whole list should be refreshed via AJAX.
 * Add items to the innerHTML of the DIV with the id "773bc02ea02b903280d609bb6a883735afbd7f14-2" - **BECAUSE:** If the user clicks the more button, further items should be added to the list
 * Completely refresh the innerHTML of the DIV with id "773bc02ea02b903280d609bb6a883735afbd7f14-3" - **BECAUSE:** In both cased the more button should be updated in order to have the correct page number as param

What is still missing is an information on when these actions are to be executed.
This done by setting some params to the links and forms with the additionalParams-attribute. You can combine the following params with custom params for your extension.
 ```
additionalParams="{your_stuff: '{key: value}', rkw_ajax: '{key: ajaxHelper.key, cid: ajaxHelper.contentUid, idl: \'2,3\'}'}"
 ```
Let's take a look on the params and what they do:
* key: Just add `ajaxHelper.key` here. This is a internal generated key that is meant to prevent collisions between extensions and plugins.
* cid: Just add `ajaxHelper.contentUid` here. This is the uid of the current content element in order to include flexform settings.
* idl: This is the magical part. Here you add a list of Ajax-Ids that are to be updated via Ajax. 

In our
``` 
<div class="filters">
    <f:form noCacheHash="true" action="list" controller="More" extensionName="RkwRelated" pluginName="Morecontent" class="ajax" 
        additionalParams="{rkw_ajax : '{key: ajaxHelper.key, cid: ajaxHelper.contentUid, idl: \'1\'}'">
        <select name="filter1">
            <option value="1">Value 1</option>
            <option value="2">Value 2</option>
        </select>
        <button type="submit">Send</button>
    </f:form>    
</div>
<rkwAjax:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="replace" ajaxId="1">
    <div class="list">
        <rkwAjax:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="append" ajaxId="2">
            <div class="list__inner">
                <div class="list__item">
                    Item 1
                </div>
                <div class="list__item">
                    Item 3
                </div>
                <div class="list__item">
                    Item 3
                </div>
            </div>
        </rkwAjax:ajaxWrapper>

        <rkwAjax:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="replace" ajaxId="3">
            <f:link.action action="list" controller="More" extensionName="RkwRelated" pluginName="Morecontent" 
                class="ajax"
                title="<f:translate key='partials.default.more.boxes.more.labelMore' extensionName='RkwRelated' />"
                rel="nofollow" target="_blank"
                additionalParams="{tx_rkwrelated_morecontent: '{pageNumber: pageNumber, filter: filter}', rkw_ajax : '{key: ajaxHelper.key, cid: ajaxHelper.contentUid, idl: \'2,3\'}'}">
                    <f:translate key="partials.default.more.boxes.more.labelMore" extensionName="RkwRelated" />
            </f:link.action>
        </rkwAjax:ajaxWrapper> 
    </div> 
</rkwAjax:ajaxWrapper>    
 ```

This way the FORM with the param `idl=1` will take care of the AjaxWrapper with `id=1` and this replace all the innerHTML of the first DIV when submitted.
Therefor if the user filters the items, he will get a new list and a new more button.

The more- link with param `idl=2,3` will take care of the AjaxWrapper with `id=2` and `id=3`, when clicked.
In case of the AjaxWrapper with `id=2` the new content will be added to the innerHTML of the first DIV (e. g. the second page of the list).
In case of the AjaxWrapper with `id=3` the button itself will be replaced with an new one, which now contains the correct params to load the third page.

Lust but not least: don't forget to add the CSS-class `ajax` to all elements that are meant to trigger AJAX-events ;-)

That is basically all the magic. 