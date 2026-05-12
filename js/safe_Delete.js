//https://qiita.com/engineerhikaru/items/714d27fe6f339b34c909
document.addEventListener('DOMContentLoaded', () => {
   const checkall = document.getElementById("checkAll");
   const checks = document.querySelectorAll(".checks");

   const dialog = document.getElementById("confirmDialog");
   const deleteBtn = document.getElementById("deleteBtn");
   const cancelBtn = document.getElementById("cancel");
   const okBtn = document.getElementById("ok");

   const deleteForm = document.getElementById("deleteForm");

   //全選択に関する処理
   checkall.addEventListener('click', () => {
   for (val of checks) {
      checkall.checked == true ? val.checked = true : val.checked = false;
   }
   });

   checks.forEach(element =>{
      element.addEventListener('click', () => {
         if(element.checked == false){
            checkall.checked = false;
         }
         if(document.querySelectorAll(".checks:checked").length == checks.length){
            checkall.checked = true;
         }
      });
   })

   //削除ボタンに関する処理
   deleteBtn.addEventListener('click',()=>{
      dialog.showModal();
   });

   cancelBtn.addEventListener('click',()=>{
      dialog.close();
   });

   okBtn.addEventListener('click', ()=>{
      dialog.close();
      deleteForm.submit();
   })

});