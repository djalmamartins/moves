(() => {
  const root=document.documentElement,menu=document.getElementById('context-menu'),toggle=document.querySelector('[data-context-toggle]'),toast=document.querySelector('.prototype-toast'),dialog=document.querySelector('.action-dialog');let timer;
  const feedback=message=>{toast.textContent=message;toast.hidden=false;clearTimeout(timer);timer=setTimeout(()=>toast.hidden=true,3500)};
  toggle.addEventListener('click',()=>{const open=menu.hidden;menu.hidden=!open;toggle.setAttribute('aria-expanded',String(open));if(open)document.getElementById('context-search').focus()});
  document.querySelectorAll('[data-context]').forEach(button=>button.addEventListener('click',()=>{document.querySelector('[data-context-name]').textContent=button.dataset.context;document.querySelector('[data-heading]').textContent=button.dataset.context;document.querySelector('.condo-mark').textContent=button.dataset.mark;menu.hidden=true;toggle.setAttribute('aria-expanded','false');toggle.focus();feedback(`Contexto alterado para ${button.dataset.context}.`)}));
  document.getElementById('context-search').addEventListener('input',event=>document.querySelectorAll('[data-context]').forEach(button=>button.hidden=!button.textContent.toLowerCase().includes(event.target.value.toLowerCase())));
  document.querySelector('[data-theme-toggle]').addEventListener('click',event=>{const dark=root.dataset.theme!=='dark';root.dataset.theme=dark?'dark':'light';event.currentTarget.setAttribute('aria-pressed',String(dark));event.currentTarget.setAttribute('aria-label',dark?'Ativar modo claro':'Ativar modo escuro')});
  document.querySelectorAll('[data-feedback]').forEach(button=>button.addEventListener('click',()=>feedback(button.dataset.feedback)));
  document.querySelectorAll('[data-action]').forEach(button=>button.addEventListener('click',()=>{dialog.querySelector('h2').textContent=button.dataset.action;dialog.querySelector('[data-action-copy]').textContent=`${button.dataset.action} em ${document.querySelector('[data-context-name]').textContent}.`;dialog.showModal()}));
  dialog.addEventListener('close',()=>{if(dialog.returnValue==='confirm')feedback('Ação simulada com sucesso; nenhum dado real foi alterado.')});
  document.addEventListener('click',event=>{if(!menu.hidden&&!event.target.closest('.context-menu')&&!event.target.closest('[data-context-toggle]')){menu.hidden=true;toggle.setAttribute('aria-expanded','false')}});
  document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!menu.hidden){menu.hidden=true;toggle.setAttribute('aria-expanded','false');toggle.focus()}});
})();
