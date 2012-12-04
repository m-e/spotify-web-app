function updateSearch(search) {
    if(search.length === 0) {
        hideSearch();
        return;
    }
    var spotifyAPI = "http://ws.spotify.com/search/1/track.json";
    $.ajax({
        "type": "GET",
        "url": spotifyAPI,
        "data": {q: search},
        "datatype": "html",
        "success": function(data) {
            if(typeof data === 'object') {
                console.log(data);
                showTracks(filterGB(data));
            }
       }
    });
}
//Controller function managing all movements. The only one that needs to be called
function moveRow(id, distance) {
    var o = $('#' + id);
    o.css('z-index', 1);
    lift(o, distance);
}
function lift(o, distance) {
    o.animate({
        top: (parseInt(o.css('top')) - 2) + 'px',
        left: (parseInt(o.css('left')) - 2) + 'px'
    }, {
        duration: 200,
        queue: false,
        complete: function() {
            moveUp(o, distance);
            moveRows(o, distance);
        }
    });
}
function moveRows(o, total) {
    var rows = o.prevAll().each(function(e) {
        console.log(this);
        $(this).animate({
            top: (parseInt($(this).css('top')) + 25) + 'px'
        }, {
            duration: 300,
            queue: false
        });
    });
}

function moveUp(o, distance) {
    o.animate({
        top: (parseInt(o.css('top')) - 25*distance)
    }, {
        duration: 800,
        queue: false,
        complete: function() {
            drop(o);
        }
    });
}

function drop(o) {
    o.animate({
        top: (parseInt(o.css('top')) + 2) + 'px',
        left: (parseInt(o.css('left')) + 2) + 'px'
    }, {
        duration: 200,
        queue: false,
        complete: function() {
            o.css('z-index', 0);
            reloadTable();
        }
    });
}

//jQuery uses colons for other functions. We need to remove them from our IDs
function sanitizeID(ID) {
    return ID.replace('/\:/g','---');
}
function unsanitizeID(ID) {
    return ID.replace(new RegExp('---', 'g'), ':');
}

function formatTime(time) {
    var hours = 0
    var minutes = 0
    var seconds = 0;
    while(time > 60*60) {
        hours++;
        time -= 60*60;
    }
    while(time > 60) {
        minutes++;
        time -= 60;
    }
    time = Math.round(time);
    
    if(time < 10) time = '0' + time;
    
    if(hours > 0) {
        if(minutes < 10) minutes = '0' + minutes;
        return hours + ':' + minutes + ':' + time;
    } else {
        return minutes + ':' + time;
    }
}

//There are a whole bunch of tracks that aren't available in the UK, and Spotify is a scumbag and won't let me filter the API
function filterGB(data) {
    for(d in data.tracks) {
        if(data.tracks[d].album.availability.territories.indexOf('GB') > 0) delete data.tracks[d];
    }
    console.log(data);
    return data;
}

function showTracks(data) {
    var html = "<table id='search-results-table'><thead><tr><th>Track Name</tj><th>Artist</tj><th>Popularity</tj><th>Time</tj><th>Album</tj></tr></thead><tbody>";
    var limit = (data.tracks.length > 20) ? 20 : data.tracks.length;
    var row = 'even';
    var current = 0;
    for(t in data.tracks) {
        row = (row === 'even') ? 'odd' : 'even';
        html += "<tr id='" + sanitizeID(data.tracks[t].href) + "' class='row" + row + "'><td>" + data.tracks[t].name + "</td><td>" + data.tracks[t].artists[0].name + "</td><td>";
        html += "<span class='popularity'><span class='popularity-value' style='width=\"" + data.tracks[t].popularity*100 + "%\"'></span></span></td><td>";
        html += formatTime(data.tracks[t].length) + "</td><td>" + data.tracks[t].album.name + "</td></tr>";
        if(current++ > limit) break;
    }
    html += "</tbody></table>";
    $(html);

    $('#search-results').html(html);
    showSearch();
    $('#search-results-table').dataTable({
        "bFilter": false
    });
    addTableEvents();
}

function addTableEvents() {
    $('#search-results-table tbody tr').on('dblclick', function() {
        addSong($(this).attr('id'));
        $(this).off('dblclick');
    }).on('click', function() {
        addClickButton($(this).attr('id'));
        $(this).addClass("selected").siblings().removeClass("selected");
    }).on('mouseover', function() {
        addClickButton($(this).attr('id'));
    });
    
}
function addClickButton(id) {
    //For some reason, jQuery keeps messing up here, so resort back to normal JS
    $('.clickToAdd').remove();
    document.getElementById(id).firstChild.innerHTML += "<input type='button' class='clickToAdd' onclick='alert(addSong(\'" + id + "\'););'/>";
}

function hideSearch() {
    $('html').off('click');
    $('#search').slideUp();
}
               
function showSearch() {       
    $('html').on('click',function(event) {
        if(!$(event.target).closest('#search').length) {
            hideSearch();
        }
    });
    
    $('#search').slideDown();
}

function addSong(songid) {
    console.log(songid);
    $.ajax({
        type: "GET",
        url: "ajax.php",
        data: {
            action: 'add',
            track: songid
        },
        success: function(data){
            alert('track added');
        }
    })
}

// 1 = up, 0 = down
function vote(direction, id) {
    if(direction !== 1 && direction !== 0) return;
    console.log(id + ' Voting: ' + direction);
    $.ajax({
        type: "GET",
        url: "ajax.php",
        data: {
            action: 'vote',
            track: unsanitizeID(id),
            direction: direction
        },
        success: function(votedata) {
            $('#score-' + id).html(votedata);
        }
    })
}

function reloadTable() {
    $.ajax({
        type: "GET",
        url: "ajax.php",
        data: {
            action: 'table'
        },
        success: function(table) {
            $('#table-container').html(table);
        }
    })
}