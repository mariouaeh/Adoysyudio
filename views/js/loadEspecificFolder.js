//carga la img con efecto 360
function fn(img) {
  let dataVar = {'especificFolder' : img};
  const configVar = {
    method: 'POST',
    body: JSON.stringify(dataVar),
    headers:{
      'Content-Type': 'application/json'
    }
  };
  let href = window.location.href;
  fetch(href+"../modules/Adoystudio_Home/library/ws_original.php",configVar)
  .then(response => response.json())
  .then(responseJson => {
    readFolders(responseJson);
  })
  .catch(err => {
    console.log(err)
  });
}
