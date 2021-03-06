
<br>

<div id="saved">Saved</div>
<div id="error">Error</div>

<div class="container admin-page">
    <div class="admin-stats container aln-c">
        <div class="stats-bubble"><span title="<{var::fullpagecount}>"><{var::pagecount}></span> Pages</div>
        <div class="stats-bubble"><span title="<{var::fullviewcount}>"><{var::viewcount}></span> Page Views</div>
    </div>

    <div class="admin-links">
        <a href="<{var::adminuri}>/pages" class="adminlink"><i class="fa fa-file"></i><span>Manage Pages</span></a>
        <a href="<{var::adminuri}>/creds" class="adminlink"><i class="fa fa-sign-in"></i><span>Manage Login</span></a>
        <a href="<{var::adminuri}>/uploads" class="adminlink"><i class="fa fa-upload"></i><span>Manage Uploads</span></a>
        <a href="<{var::adminuri}>/update" class="adminlink"><i class="fa fa-wrench"></i><span>Update Phroses</span></a>
    </div>

    <br>

    <div class="aln-c">
        <div class="form_icfix aln-l c site-namer panel-input">
            <div>Site Name:</div>
            <input class="c form_field form_input" type="text" value="<{site::name}>" data-method="POST" data-url="">
        </div>
        
        <div class="form_icfix aln-l c siteurl-changer panel-input">
            <div>Site URL:</div>
            <input class="c form_field form_input" type="text" value="<{var::host}>" data-method="POST" data-url="">
        </div>
        
        <div class="form_icfix aln-l c theme-select panel-input">
            <div>Theme:</div>
            <select class="c form_field form_select" id="theme-selector" data-method="POST" data-url="">
                <{array::themes::<option value="@name" @selected>@name</option>}>
                
            </select>
        </div>

        <div class="form_icfix aln-l c admin-uri panel-input">
            <div>Admin URI:</div>
            <input class="c form_field form_input" type="text" value="<{var::adminuri}>" data-method="POST" data-url="" data-initial-value="<{var::adminuri}>">
        </div>

        <div class="form_icfix aln-l c admin-ip panel-input">
            <div>Admin IPs:</div>
            <input class="c form_field form_input" type="text" value="<{var::adminip}>" data-method="POST" data-url="" placeholder="Comma separated IP Addresses or blank for no restrictions.">
        </div>

        <div class="form_icfix aln-l c maintenance-select panel-input">
            <div>Maintenance:</div>
            <select class="c form_field form_select" data-method="POST" data-url="">
                <{array::moption::<option value="@value" @selected>@name</option>}>
            </select>
        </div>
    </div>
</div>