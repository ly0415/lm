<style>
    body, div
    {
        font-size: 0.9em;
        font-family: verdana;
    }
    .copyright
    {
        text-align: center;
        font-size: 1em;
    }
    .tips
    {
        border: 1px solid #666666;
        padding: 5px 10px 5px 10px;
        position: absolute;
        background-color: #ffffff;
        filter:alpha(Opacity=80);
    }
</style>


<br><br><br><br><br><br>
<SCRIPT LANGUAGE="JavaScript">
    <!--
    document.onmouseover = document.onmousemove = fnHandleOver;
    document.onmouseout = fnHandleOut;


    var _tips = document.createElement("DIV");
    _tips.className = "tips";
    document.body.appendChild(_tips);


    function fnHandleOver() {
        var El = event.srcElement;
        var Tips = El.getAttribute("tips");
        if (!Tips) { _tips.style.display = "none"; return; }
        with (_tips.style) {
            display = "";
            left = event.clientX + 10;
            top = event.clientY - 70;
        }
        _tips.innerHTML = Tips;
    }
    function fnHandleOut() {
        _tips.style.display = "none";
    }
    //-->
</SCRIPT>
<h4 class="copyright" tips="显示信息  "> 鼠标移过来 </h4>
<script type='text/javascript' src="jquery3.2.1.min.js"></script>