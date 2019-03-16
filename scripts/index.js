function click_block(row,column, elem) {
    console.log("click: "+"row="+row+", column="+column);
    $.ajax({
        type: "POST",
        url: "sweep.php",
        data: {
            type:"click",
            row:row,
            column:column,
        },
        dataType: "json",
        cache: false,
        success: function (data) {
            $("#message_echo").text(data['message']);
            if(data["status"]=="ok" || data["status"]=="over" || data["status"]=="finish"){
                console.log("click ajax request return success");
                $("#rows").val(data["rows"]);
                $("#columns").val(data["columns"]);
                $("#shovel_num").text("Left Shovels: "+data["shovels"]);
                $("#grid tr").remove();
                let i = 0;
                let newRow = "";
                let cur_row = 1;
                $.each(data["grid"],function(n,v) {
                    //console.log(v);
                    if (i == 0){
                        newRow = "<tr>";
                    }
                    i ++;
                    newRow += "<td class='grid_cell' onclick='click_block("+cur_row+","+i+","+
                        "$(this))' oncontextmenu='put_shovel("+cur_row+","+i+","+"$(this))'>";
                    if (v > 0 ){
                        newRow += "<p class='cell_near_mine'>"+v+"</p></td>";
                    }else if(v == 0){
                        newRow += "<p class='cell_nothing'></p></td>";
                    }else if(v == -2){
                        newRow += "<p class='cell_shovel'>√</p></td>";
                    }else if(v == -3){
                        newRow += "<p class='cell_mine'>*</p></td>";
                    }else{
                        newRow += "<p class='cell_unknown'>&nbsp;</p></td>";
                    }
                    if (i == data['columns']){
                        i = 0;
                        cur_row ++;
                        newRow += "</tr>";
                        //console.log("newRow="+newRow);
                        $("#grid").append(newRow);
                    }
                });
                if(data["status"]=="over"){
                    //$('#message_echo').append(data['grid']);
                    alert("Game Over!");
                }else if(data["status"]=="finish"){
                    alert("Congratulations! You have completed this mission!");
                }
            } else{
                console.log("data:"+data['message']);
            }
            $("tr").bind("contextmenu", function(){return false;});
        },
        error:function(xhr){
            alert(xhr);
        }
    });
}

function put_shovel(row,column, elem) {
    console.log("shovel: "+"row="+row+", column="+column);
    $.ajax({
        type: "POST",
        url: "sweep.php",
        data: {
            type:"shovel",
            row:row,
            column:column,
        },
        dataType: "json",
        cache: false,
        success: function (data) {
            $("#message_echo").text(data['message']);
            $("#shovel_num").text("Left Shovels: "+data["shovels"]);
            if("ok" == data['status']){
                elem.text("");
                elem.append("<p class='cell_shovel'>√</p>");
            }else if("recycle" == data['status']){
                elem.text("");
            }else if(data["status"]=="finish"){
                elem.text("");
                elem.append("<p class='cell_shovel'>√</p>");
                alert("Congratulations! You have completed this mission!")
            }
        },
        error:function(xhr){
            alert(xhr);
        }
    });
}

function start_new_game(){
    $.ajax({
        type:"POST",
        url:"sweep.php",
        data:$('#menu').serialize(),
        dataType :"json",
        cache:false,
        success:function(data){
            $("#message_echo").text(data['message']);
            if(data["status"]=="ok" || data["status"]=="continue"){
                console.log("start_new_game ajax request return ok");
                $("#rows").val(data["rows"]);
                $("#columns").val(data["columns"]);
                $("#shovel_num").text("Left Shovels: "+data["shovels"]);
                $("#grid tr").remove();
                let i = 0;
                let newRow = "";
                let cur_row = 1;
                $.each(data["grid"],function(n,v) {
                    //console.log("n="+n+"  v="+v);
                    //console.log("data['columns']="+data['columns']);
                    if (i == 0){
                        newRow = "<tr>";
                    }
                    i ++;
                    newRow += "<td class='grid_cell' onclick='click_block("+cur_row+","+i+","+
                        "$(this))' oncontextmenu='put_shovel("+cur_row+","+i+","+"$(this))'>";
                    if (v > 0 ){
                        newRow += "<p class='cell_near_mine'>"+v+"</p></td>";
                    }else if(v == 0){
                        newRow += "<p class='cell_nothing'></p></td>";
                    }else if(v == -2){
                        newRow += "<p class='cell_shovel'>√</p></td>";
                    }else{
                        newRow += "<p class='cell_unknown'>&nbsp;</p></td>";
                    }
                    if (i == data['columns']){
                        i = 0;
                        cur_row ++;
                        newRow += "</tr>";
                        //console.log("newRow="+newRow);
                        $("#grid").append(newRow);
                    }
                });
            }else{
                console.log("data:"+data);
            }
            $("tr").bind("contextmenu", function(){return false;});
        },
        error:function(xhr){
            alert(xhr);
        }
    });
}