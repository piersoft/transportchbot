<? php
 ?>


<!doctype html>
<html>
<head>
    <meta charset="utf-8">

    <title>Stationboard Example - Transport API</title>

    <!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="stylesheet" href="http://transport.opendata.ch/media/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="http://transport.opendata.ch/media/css/layout.css" />

    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css" />

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
    <script src="http://transport.opendata.ch/media/js/moment.min.js"></script>
    <script>

    $(function () {

        var station = '008311145';

    	function refresh() {
        	if (station) {
        		$.get('http://transport.opendata.ch/v1/stationboard', {id: station, limit: 15}, function(data) {
        		    $('#stationboard tbody').empty();
        			$(data.stationboard).each(function () {
        			    var prognosis, departure, delay, line = '<tr><td>';
        			    departure = moment(this.stop.departure);
        			    if (this.stop.prognosis.departure) {
        			        prognosis = moment(this.stop.prognosis.departure);
        			        delay = (prognosis.valueOf() - departure.valueOf()) / 60000;
        			        line += departure.format('HH:mm') + ' <strong>+' + delay + ' min</strong>';
        			    } else {
        			        line += departure.format('HH:mm');
        			    }
        			    line += '</td><td>' + this.name + '</td><td>' + this.to + '</td></tr>';
        			    $('#stationboard tbody').append(line);
        			});
        		}, 'json');
        	}
    	}

        $('#station').autocomplete({
    		source: function (request, response) {
    			$.get('http://transport.opendata.ch/v1/locations', {query: request.term, type: 'station'}, function(data) {
    				response($.map(data.stations, function(station) {
    					return {
    						label: station.name,
    						station: station
    					}
    				}));
    			}, 'json');
    		},
    		select: function (event, ui) {
    		    station = ui.item.station.id;
                refresh();
    		}
    	});

    	setInterval(refresh, 30000);
    	refresh();
    });

    </script>
    <style>
        h3 {
             margin: 40px 0 20px;
        }

        .ui-widget,
        .ui-widget input {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .ui-autocomplete {
            text-align: left;
        }

        strong {
            color: #d10505;
        }

        .column {
            float: right;
        }

        .media {
            padding-top: 30px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            padding: 8px;
            line-height: 18px;
            vertical-align: top;
            border-bottom: 1px solid #DDD;
        }

    </style>
</head>

<body>

<div class="wrapper">
    <header>
        <h1><a href="../">Transport</a></h1>
        <p><a href="http://opendata.ch/">Opendata.ch</a></p>
        <p>Adapted by @piersoft</p>
    </header>

    <article>
        <h3>Inserire una stazione ferroviaria:</h3>
        <div class="ui-widget station">
            <input id="station" value="Lecce" placeholder="Station" autofocus />
        </div>
        <div class="media">
        </div>

        <table id="stationboard">
            <colgroup>
                <col width="120">
                <col width="140">
                <col width="230">
            </colgroup>
            <thead>
                <tr>
                    <th align="left">orario</th>
                    <th>&nbsp;</th>
                    <th align="left">destinazione</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </article>
</div>

</body>
</html>
