<h1>$Event.Title</h1>
<% if $ZipDownloadLink %>
    <a href="$ZipDownloadLink" class="download-all-btn">Alle downloaden</a>
<% end_if %>
<div class='event-images'>
    <% loop $Event.Photos %>
        <div class='event-image'>
            <a href="$Image.URL" target="_blank">$Image.Fill(200,200)</a>
        </div>
    <% end_loop %>
</div>
