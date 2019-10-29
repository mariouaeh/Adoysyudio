function loadSprites(webservice) {
  let dataVar = new FormData();
  dataVar.append('getSprites', {1:1});
  const configVar = {
    method: 'POST',
    headers: {
      Accept: 'application/json',
    },
    body: dataVar,
  };

  fetch(webservice, configVar).then(response => response.json())
  .then(responseJson => {
    readFolders(responseJson, 3);
  })
  .catch(err => {
    console.log(err)
  });

}

function readFolders(responseJson, limit) {

  let adoySprites                   = document.getElementById("adoySprites");
  adoySprites.style.display         = 'flex';
  adoySprites.style.alignItems      = 'center';
  adoySprites.style.justifyContent  = 'center';
  adoySprites.style.width           = '100%';

  let folders = responseJson.folders;
  let indice = 0;
  for (nameFolder in folders){
    let group = folders[nameFolder];
    let groupDiv = document.createElement("div");
    groupDiv.setAttribute("name", nameFolder);
    groupDiv.setAttribute("class", 'adoyStudio360');
    groupDiv.setAttribute("index", "0");
    groupDiv.setAttribute("total", folders[nameFolder].length);
    groupDiv.setAttribute("mouseisclick", "0");
    groupDiv.setAttribute("zoom", "1");

    groupDiv.setAttribute("onmouseover", "mouseisclick(this,1)");
    groupDiv.setAttribute("ontouchstart", "mouseisclick(this,1)");
    groupDiv.setAttribute("onmouseout", "mouseisclick(this,0)");
    groupDiv.setAttribute("ontouchend", "mouseisclick(this,0)");
    groupDiv.setAttribute("onmousemove", "mouseMove(this)");
    groupDiv.setAttribute("ontouchmove", "mouseMove(this)");
    groupDiv.setAttribute("ondblclick", "zoom(this)");

    groupDiv.style.display = "flex";
    groupDiv.style.alignItems = "center";
    groupDiv.style.justifyContent = "center";

    adoySprites.appendChild(groupDiv);
    loadImgGroup(adoySprites, group, nameFolder, 0, groupDiv);

    indice = indice+1;
    if (limit<indice+1) {
      break;
    }
  }//end for
}//end function readFolders

function loadImgGroup(adoySprites, group, groupName, index, groupDiv) {
  //let src = window.location.href+'spritesadoystudio/'+group[index];
  //let src = href+'../modules/Adoystudio_Home/spritesadoystudio/'+group[index];
  let src = group[index];
  let img = new Image();
  img.src = src;
  img.onload = function () {
    if (index < group.length) {
      display = "none";
      if (index === 0) {
        display = "block";
      }
      div360(groupDiv, groupName, src, index, display);

      if (index === group.length -1) {
        autoMove(groupDiv,0)
      }

      if (index < group.length-1){
        loadImgGroup(adoySprites, group, groupName, index+1, groupDiv);
      }

    }
  }
}

function div360(groupDiv, groupName, srcImg, index, display) {
  let img = document.createElement("img");
  img.setAttribute("id", "adoy360"+groupName+index);
  img.setAttribute("src", srcImg);
  img.style.display = display;
  img.style.width = '100%';
  img.style.height = 'auto';
  groupDiv.appendChild(img);
}

function mouseisclick(groupDiv, val) {
  groupDiv.setAttribute("mouseisclick", val);
}

function autoMove(groupDiv, before) {
  let mouseisclick = groupDiv.getAttribute("mouseisclick");
  //if (mouseisclick === "0") {
    let cantidadFotos = groupDiv.getAttribute("total");
    let t = 80;
    if (cantidadFotos<36) t = 3000 / parseFloat(cantidadFotos);

    setTimeout(function(){
      turn(groupDiv, before, before+1);
      autoMove( groupDiv, before+1 );
    },t);
  //}
}

function turn(groupDiv, before, after) {
  let index = groupDiv.getAttribute("index");
  let total = groupDiv.getAttribute("total");
  let newIndex = 0;

  if (before<after) {
    newIndex = parseInt(index) + 1;
  }
  else{
    newIndex = parseInt(index) - 1;
  }

  if (newIndex <= -1) {
    newIndex = total;
  }
  if (newIndex >= total) {
    newIndex = 0;
  }

  groupDiv.setAttribute("index", newIndex);

  let name  = groupDiv.getAttribute("name");

  let imgNow = document.getElementById("adoy360"+name+index);
  let imgNew = document.getElementById( "adoy360"+name+newIndex );

  imgNow.style.display = "none";
  imgNew.style.display = "block";
}

function mouseMove(groupDiv) {
  let newX = event.pageX===undefined ? event.touches[0].clientX :
event.pageX;
  turn(groupDiv, parseInt(groupDiv.getAttribute("move")), newX);
  groupDiv.setAttribute("move",newX);
}

function zoom(div) {
  let val = div.getAttribute("zoom");
  val = parseInt(val) + 1;
  if (val==4) val = 1;
  div.style.transform = "scale("+val+")";
  div.setAttribute("zoom", val);
  return true;
}
