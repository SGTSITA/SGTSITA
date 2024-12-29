<!DOCTYPE html>
<html lang="en" >
    <!--begin::Head-->
    <head>
        <title>SGT Sita</title>
        <meta charset="utf-8"/>
        
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta property="og:locale" content="es" />
        <meta property="og:type" content="article" />
        <!--begin::Fonts(mandatory for all pages)-->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"/>        <!--end::Fonts-->

        
        
                    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
                            <link href="/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css"/>
                            <link href="/assets/css/style.bundle.css" rel="stylesheet" type="text/css"/>
                        <!--end::Global Stylesheets Bundle-->
    </head>
    <!--end::Head-->

    <!--begin::Body-->
    <body  id="kt_body"  class="app-blank" >
        <!--begin::Theme mode setup on page load-->
<script>
	var defaultThemeMode = "light";
	var themeMode;

	if ( document.documentElement ) {
		if ( document.documentElement.hasAttribute("data-bs-theme-mode")) {
			themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
		} else {
			if ( localStorage.getItem("data-bs-theme") !== null ) {
				themeMode = localStorage.getItem("data-bs-theme");
			} else {
				themeMode = defaultThemeMode;
			}			
		}

		if (themeMode === "system") {
			themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
		}

		document.documentElement.setAttribute("data-bs-theme", themeMode);
	}            
</script>
<!--end::Theme mode setup on page load-->
                            
        <!--begin::Root-->
<div class="d-flex flex-column flex-root" id="kt_app_root">
    


        <!--begin::Email template-->      
		<style>
            html,body {
                padding:0;
                margin:0;
                font-family: Inter, Helvetica, "sans-serif";                                       
            }            

			a:hover {
                color: #009ef7;
            }
        </style>
        
        <div id="#kt_app_body_content" style="background-color:#D5D9E2; font-family:Arial,Helvetica,sans-serif; line-height: 1.5; min-height: 100%; font-weight: normal; font-size: 15px; color: #2F3044; margin:0; padding:0; width:100%;">
            <div style="background-color:#ffffff; padding: 45px 0 34px 0; border-radius: 24px; margin:40px auto; max-width: 600px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" height="auto" style="border-collapse:collapse">
                    <tbody>                      
                        <tr>
                            <td align="center" valign="center" style="text-align:center; padding-bottom: 10px">
                                
    <!--begin:Email content-->
    <div style="text-align:center; margin:0 15px 34px 15px">
        <!--begin:Logo-->
        <div style="margin-bottom: 10px">
          
        </div>
        <!--end:Logo-->

        <!--begin:Media-->
 
        <!--end:Media-->                            

        <!--begin:Text-->
        <!--div style="font-size: 14px; font-weight: 500; margin-bottom: 27px; font-family:Arial,Helvetica,sans-serif;">
            <p style="margin-bottom:9px; color:#181C32; font-size: 22px; font-weight:700">Hey Marcus, thanks for signing up!</p>
            <p style="margin-bottom:2px; color:#7E8299">Lots of people make mistakes while creating</p>
            <p style="margin-bottom:2px; color:#7E8299">paragraphs. Some writers just put whitespace in</p>  
            <p style="margin-bottom:2px; color:#7E8299">their text in random places</p>  
        </div-->  
        <!--end:Text-->
         
         
    </div>
    <!--end:Email content-->    
                            </td>
                        </tr>  

                         
                            <tr style="display: flex; justify-content: center; margin:0 60px 35px 60px">
                                <td align="start" valign="start" style="padding-bottom: 10px;">
                                    <p style="color:#181C32; font-size: 18px; font-weight: 600; margin-bottom:13px">Datos del contenedor</p>

                                    <!--begin::Wrapper-->
                                    <div style="background: #F9F9F9; border-radius: 12px; padding:35px 30px">
                                                                                    <!--begin::Item-->
                                            <div style="display:flex">                    
                                                <!--begin::Media-->
                                                <div style="display: flex; justify-content: center; align-items: center; width:40px; height:40px; margin-right:13px">
                                                        <span style="position: absolute; color:#50CD89; font-size: 16px; font-weight: 600;">
                                                            1
                                                        </span> 
                                                                                                 
                                                </div>
                                                <!--end::Media-->                   

                                                <!--begin::Block-->
                                                <div>
                                                    <!--begin::Content-->
                                                    <div>
                                                        <!--begin::Title-->
                                                        <a href="#" style="color:#181C32; font-size: 14px; font-weight: 600;font-family:Arial,Helvetica,sans-serif">Núm. Contenedor: {{$contenedor->num_contenedor}}</a>
                                                        <!--end::Title-->                                    
                                                    </div>
                                                    <!--end::Content-->  
                                                    
                                                                                                            <!--begin::Separator-->
                                                        <div class="separator separator-dashed" style="margin:17px 0 15px 0"></div>
                                                        <!--end::Separator-->
                                                                                       
                                                </div>
                                                <!--end::Block-->  
                                            </div>                                           
                                            <!--end::Item-->                                          
                                                                                    <!--begin::Item-->
                                            <div style="display:flex">                    
                                                <!--begin::Media-->
                                                <div style="display: flex; justify-content: center; align-items: center; width:40px; height:40px; margin-right:13px">
                                                        <span style="position: absolute; color:#50CD89; font-size: 16px; font-weight: 600;">
                                                            2
                                                        </span> 
                                                                                                 
                                                </div>
                                                <!--end::Media-->                   

                                                <!--begin::Block-->
                                                <div>
                                                    <!--begin::Content-->
                                                    <div>
                                                        <!--begin::Title-->
                                                        <a href="#" style="color:#181C32; font-size: 14px; font-weight: 600;font-family:Arial,Helvetica,sans-serif">Origen: {{$contenedor->origen}}</a>
                                                        <!--end::Title-->
                                   
                                                    </div>
                                                    <!--end::Content-->  
                                                    
                                                                                                            <!--begin::Separator-->
                                                        <div class="separator separator-dashed" style="margin:17px 0 15px 0"></div>
                                                        <!--end::Separator-->
                                                                                       
                                                </div>
                                                <!--end::Block-->  
                                            </div>                                           
                                            <!--end::Item-->                                          
                                                                                    <!--begin::Item-->
                                            <div style="display:flex">                    
                                                <!--begin::Media-->
                                                <div style="display: flex; justify-content: center; align-items: center; width:40px; height:40px; margin-right:13px">
                                                
                                                     
                                                        <span style="position: absolute; color:#50CD89; font-size: 16px; font-weight: 600;">
                                                            3
                                                        </span> 
                                                                                                 
                                                </div>
                                                <!--end::Media-->                   

                                                <!--begin::Block-->
                                                <div>
                                                    <!--begin::Content-->
                                                    <div>
                                                        <!--begin::Title-->
                                                        <a href="#" style="color:#181C32; font-size: 14px; font-weight: 600;font-family:Arial,Helvetica,sans-serif">Destino: {{$contenedor->destino}}</a>
                                                        <!--end::Title-->
                                   
                                                    </div>
                                                    <!--end::Content-->  
                                                    
                                                                                       
                                                </div>
                                                <!--end::Block-->  
                                            </div>                                           
                                            <!--end::Item-->                                          
                                         
                                    </div> 
                                    <!--end::Wrapper-->
                                </td>
                            </tr>    
                                                
                         
                       
                        
                     
                        
                       
                    </tbody>   
                </table> 
            </div>
        </div>
        <!--end::Email template-->

 </div>
<!--end::Root-->
        
        <!--begin::Javascript-->
        <script>
            var hostUrl = "/assets/";        </script>

                    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
                            <script src="/assets/plugins/global/plugins.bundle.js"></script>
                            <script src="/assets/js/scripts.bundle.js"></script>
                        <!--end::Global Javascript Bundle-->
        
        
                <!--end::Javascript-->
    </body>
    <!--end::Body-->
</html>