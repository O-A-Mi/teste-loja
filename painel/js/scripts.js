function caminho(titulo){
    const caminhoDiv = document.getElementById('caminho');
    if (!caminhoDiv) return;

    const linkElement = caminhoDiv.querySelector('a');
    if (!linkElement) return;

    linkElement.href = 'index.html';

    const svg = linkElement.innerHTML;
    linkElement.innerHTML = svg;

    const tituloElement = document.createElement('span');
    tituloElement.className = 'text-link';
    tituloElement.textContent = `/ ${titulo}`;
    caminhoDiv.appendChild(tituloElement);
}