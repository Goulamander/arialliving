var App=function(){"use strict";var config={};return{conf:config,init:function(options){document.body.classList.add("app-ready"),$.extend(config,options),$.extend(!0,$.fn.dataTable.defaults,{processing:!0,serverSide:!0,bLengthChange:!1,responsive:!0,deferRender:!0,autoWidth:!1,stateSave:!1,dom:"Blrtip",pageLength:100,oLanguage:{oPaginate:{sPrevious:'<i class="material-icons">keyboard_arrow_left</i>',sNext:'<i class="material-icons">keyboard_arrow_right</i>'},sEmptyTable:"No results found",sProcessing:"<i class='ion ion-ios-refresh table-loading'></i>"}}),$.ajaxSetup({headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},error:function(xhr,status,error){let err=eval("("+xhr.responseText+")");if(err&&err.errors){var ul="<ul>";return $.each(err.errors,function(e,t){"error"!=e&&(ul+="<li>"+t+"</li>")}),ul+="</ul>",sc.alert.show("alert-danger",ul),void($form&&_releaseSubmitBtn($form))}}})},helpers:{formatAddress:e=>{if(e)return`${e.street_address_1} ${e.suburb}, ${e.postcode} ${e.state}`}}}}();let request;sc={alert:{show:function(e,t,a=2e3){let r=document.querySelectorAll('.alert[role="alert"]');r&&r.forEach(e=>$(e).alert("close"));let o=`\n                <div class="alert alert-icon alert-dismissible ${e}" role="alert">\n                    <div class="message">\n                        <button type="button" data-dismiss="alert" aria-label="Close" class="close">\n                            <i class="material-icons" aria-hidden="true">close</i>\n                        </button>\n                        ${t}\n                    </div>\n                </div>`,n=$.parseHTML(o.allTrim());setTimeout(function(){$(n).fadeOut(500,function(){$(this).alert("close")})},a),$("body").append(n)}}},$(function(){$(document).on("click",".selected-color",function(){location_id=$(this).data("id"),$(this).parents("tr").toggleClass("highlight"),$(this).parents("tr").siblings().removeClass("highlight"),function(e,t){$(".color-choices").addClass("active"),$(".color-choices").find("input").prop("checked",!1),e.attr("data-color")&&$(".color-choices").find("input[data-color="+e.attr("data-color")+"]").prop("checked",!0);$(".color-choices").find("label").unbind().on("click",function(){let a=$(this).attr("data-color");$.ajax({dataType:"json",url:"/location/update/"+t,type:"POST",data:{color:a},success:function(t){sc.alert.show("alert-success",t.message),e.attr("style","background-color:#"+a),e.attr("data-color",a)}})})}($(this),location_id)}),$(document).keydown(function(e){27==e.keyCode&&$(".color-choices").hasClass("active")&&$(".color-choices").removeClass("active")}),$(document).on("click","#closeColorPicker",function(e){$(".color-choices").removeClass("active")})}),$("form").parsley({errorsContainer:function(e){return e.$element.closest(".form-group")}}),$(".mobile-number").inputmask("9999 999 999",{placeholder:"____ ___ ____"}),$(".phone-number").inputmask("(09) 9999 9999",{placeholder:"(0_) ____ ____"}),$(".credit-input").inputmask("9999 9999 9999 9999",{placeholder:"xxxx-xxxx-xxxx-xxxx"}),$(function(){$(document).on("click",'button[type="submit"]',function(e){let t=$(this).parents("form");t.parsley().isValid()?(e.preventDefault(),$(this).prop("disabled",!0).addClass("loading"),SubmitForm(t)):myapp.booking.fixSliderHight()})});const SubmitForm=e=>{let t=myapp.form.collectInputs(e);t.action_route=e.attr("action")||"";let a=e[0].querySelectorAll("._html_content");if(a&&a.forEach(e=>{t[e.dataset.name]=e.quill.root.innerHTML}),$(e).find("._full_html_editor").length){let a=$(e).find("._full_html_editor")[0];t[a.dataset.name]=a.quill.root.innerHTML}if(!1!==t){if("multipart/form-data"===e.attr("enctype")){let a=new FormData;return Object.entries(t).forEach(([e,t])=>{a.append(e,t)}),e.find("input[type=file]").each(function(){a.set($(this).attr("name"),$(this)[0].files[0])}),StoreData({action_route:t.action_route,data:a},e),!1}StoreData(t,e)}else e.find('button[type="submit"]').prop("disabled",!1).removeClass("loading")},StoreData=function(e,t=null){let a=e;t&&"multipart/form-data"===t.attr("enctype")&&(a=e.data),axios.post(e.action_route,a).then(function(e){if(!e.data)return!1;if(void 0!==window.dataTable&&window.dataTable.DataTable().ajax.reload(),void 0!==window.AdminCalendar&&myapp.calendar.CalendarRefetch(),"ResidentBooking"in e.data.data)return"redirect_to"in e.data.data?void(window.location.href=e.data.data.redirect_to):void myapp.booking.afterBookingSubmission(e.data.data.ResidentBooking);if("PDFUpdate"in e.data.data&&myapp.fileManager.updatePDFListAfterUpdate(e.data.data),t){if(t[0].dataset.reload&&"true"==t[0].dataset.reload)return void window.location.reload();let e=t.parents(".modal");e.length&&(t[0].reset(),e.modal("hide")),_releaseSubmitBtn(t);let a=t.attr("id");if(a&&"ConfirmPasswordForm"===a)return void SubmitForm($("#BookingForm"))}sc.alert.show("alert-success",e.data.message||"Successful update")}).catch(function(e){_errorResponse(e),t&&_releaseSubmitBtn(t)})},_releaseSubmitBtn=function(e){e&&e.find('button[type="submit"]').removeClass("loading").attr("disabled",!1)},editData=function(e,t,a){let r="";switch(t){case"location":r="/location/"+a;break;default:return void(r="")}r&&$.ajax({dataType:"json",url:r,type:"GET",success:function(t){t.error?sc.alert.show("alert-danger","Something went wrong. "+t.error):($(e).find("form").removeClass("jsSubmit"),$.each(t.data,function(t,a){var r=$(e).find('[name="'+t+'"]');if(r.length)switch(r.attr("type")){case"radio":case"checkbox":r.each(function(){$(this).val()==a&&$(this).attr("checked",!0)});break;case"date":a&&"-0001-11-30 00:00:00"!==a&&r.val(moment(a,"YYYY-MM-DD").format("YYYY-MM-DD"));break;default:r.val(a)}}))}})};if("undefined"!=typeof Sortable){var el=document.getElementById("locationsOrder");el&&Sortable.create(el,{sort:!0,group:"words",animation:150,handle:".drag-handle",onUpdate:function(e){__saveOrder(e)}})}var __saveOrder=function(evt){let Rows=new Array;$("#locationsOrder > tr").each(function(){Rows[$(this).index()]=$(this).attr("id")}),$.ajax({url:"/location/order",type:"POST",data:{order:Rows,location_id:evt.item.id,location_index:evt.newIndex},cache:!1,success:function(e,t){e.error?sc.alert.show("alert-danger","Something went wrong. "+e.error):("undefined"!=typeof DataTable_LocationList&&DataTable_LocationList.DataTable().ajax.reload(),sc.alert.show("alert-success",e.message||"Successful update"))},error:function(xhr,status,error){var err=eval("("+xhr.responseText+")");if(err.errors){var ul="<ul>";return $.each(err.errors,function(e,t){"error"!=e&&(ul+="<li>"+t+"</li>")}),ul+="</ul>",sc.alert.show("alert-danger",ul),void($form&&_releaseSubmitBtn($form))}}})};
