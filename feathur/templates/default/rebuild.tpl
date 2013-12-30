{%foreach vps in VPS}
<script type="text/javascript">
    $(document).ready(function () {
        $(function () {
            $("#tabs").tabs();
        });
        var counttx = 0;
        var counterrx = setInterval(timerrx, 1000);

        function timerrx() {
            counttx = counttx + 1;
            $('#timer').html(counttx);
        }

        function rebuildcheck() {
            $(function () {
                $.getJSON("view.php?id={%?vps[id]}&action=rebuildcheck", function (result) {
                    if (result.reload == 1) {
                        location.reload();
                        counttx = 0;
                    } else {
                        counttx = 0;
                    }
                });
            });
        }

        setInterval(rebuildcheck, 5000);
    });
</script>
<br><br>
<div align="center">
    <div id="tabs" style="width:95%">
        <ul>
            <li><a href="#tabs-1">Rebuild</a></li>
        </ul>
        <div id="tabs-1">
            <div align="center">
                <div class="albox warningbox" style="width:50%">Your VPS Is Currently Being Rebuilt</div>
            </div>
            <div align="center">
                Your VPS is being rebuilt. This page will update approximately every 5 seconds...
            </div>
            <br>

            <div align="center" style="width:30px;display:inline;white-space:nowrap;">Last update: <a id="timer"
                                                                                                      style="white-space:nowrap;">0</a>
                seconds ago
            </div>
            <br><img src="templates/default/img/loading/7.gif">
        </div>
    </div>
</div>
{%/foreach}