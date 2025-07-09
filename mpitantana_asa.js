// Fari-pitsinjovana ankapobe
let kilasy_ankehitriny = '';
let asa_hofafana = null;

// Fonction ampidirana antsipiriany kilasy
function ampidiro_antsipiriany_kilasy(anarana_kilasy) {
    kilasy_ankehitriny = anarana_kilasy;
    
    // Aseho ny famantarana fampidirana
    aseho_toe_fampidirana();
    
    // Atao ny antso AJAX haka ny asa
    fetch(`haka_asa_kilasy.php?kilasy=${encodeURIComponent(anarana_kilasy)}`)
        .then(response => response.json())
        .then(data => {
            if (data.fahombiazana) {
                aseho_antsipiriany_kilasy(anarana_kilasy, data.asa, data.statistika);
            } else {
                aseho_fampandrenesana('Fahadisoana tamin\'ny fampidirana ny asa: ' + data.hadisoana, 'hadisoana');
                console.error('Fahadisoana:', data.hadisoana);
            }
        })
        .catch(error => {
            aseho_fampandrenesana('Fahadisoana fifandraisana tamin\'ny fampidirana ny asa', 'hadisoana');
            console.error('Fahadisoana:', error);
        })
        .finally(() => {
            afeno_toe_fampidirana();
        });
}

// Fonction aseho toe fampidirana
function aseho_toe_fampidirana() {
    const fitoerana = document.getElementById('fitoerana-asa');
    if (fitoerana) {
        fitoerana.innerHTML = `
            <div class="loading-state" style="text-align: center; padding: 3rem;">
                <div class="loading-spinner" style="
                    width: 40px; 
                    height: 40px; 
                    border: 4px solid #f3f4f6; 
                    border-top: 4px solid #3b82f6; 
                    border-radius: 50%; 
                    animation: spin 1s linear infinite;
                    margin: 0 auto 1rem;
                "></div>
                <p style="color: #6b7280;">Mampiditra ny asa...</p>
            </div>
        `;
    }
}

// Fonction afeno toe fampidirana
function afeno_toe_fampidirana() {
    // Ny toe fampidirana dia hosoloina ny votoaty tena izy
}

// Fonction aseho antsipiriany kilasy
function aseho_antsipiriany_kilasy(anarana_kilasy, asa, statistika) {
    // Afeno ny seho kilasy ary aseho ny seho antsipiriany
    document.getElementById('seho-kilasy').style.display = 'none';
    document.getElementById('seho-antsipiriany-kilasy').style.display = 'block';
    
    // Havaozina ny lohateny sy ny statistika
    document.getElementById('anarana-kilasy').textContent = anarana_kilasy;
    document.getElementById('isan-asa-kilasy').textContent = `${statistika.isan_asa} asa`;
    document.getElementById('asa-misokatra-kilasy').textContent = `${statistika.asa_misokatra} misokatra`;
    document.getElementById('asa-tapitra-kilasy').textContent = `${statistika.asa_tapitra} tapitra`;
    
    // Aseho ny asa
    aseho_asa(asa);
}

// Fonction aseho asa
function aseho_asa(asa) {
    const fitoerana = document.getElementById('fitoerana-asa');
    
    if (asa.length === 0) {
        fitoerana.innerHTML = `
            <div class="karatra">
                <div class="empty-state">
                    <i class="fas fa-tasks" style="font-size: 3rem; margin-bottom: 1rem; color: #9ca3af;"></i>
                    <p>Tsy misy asa hita ho an'ity kilasy ity.</p>
                </div>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    asa.forEach(asa_iray => {
        const toe_javatra = haka_toe_javatra_asa(asa_iray.tapitra);
        const votoaty_fohy = asa_iray.votoaty.length > 150 ? 
            asa_iray.votoaty.substring(0, 150) + '...' : 
            asa_iray.votoaty;
        
        html += `
            <div class="karatra karatra-mpianatra">
                <div class="lohateny-karatra">
                    <div class="fampahalalana-mpianatra">
                        <h3>${afeno_html(asa_iray.lohateny)}</h3>
                        <p class="anarana-mpampiasa">Kilasy: ${afeno_html(asa_iray.kilasy)}</p>
                    </div>
                    <div class="salan-isa-mpianatra ${toe_javatra.kilasy}">
                        <i class="${toe_javatra.kisary}"></i>
                        ${toe_javatra.soratra}
                    </div>
                </div>
                <div class="votoaty-karatra">
                    <div class="mb-2">
                        <p style="color: #6b7280; line-height: 1.5;">${afeno_html(votoaty_fohy)}</p>
                    </div>
                    
                    <div class="lisitry-naoty">
                        <div class="singa-naoty">
                            <div class="fampahalalana-naoty">
                                <div class="taranja-naoty">
                                    <i class="fas fa-calendar-plus"></i>
                                    Nalefa: ${asa_iray.daty_fampidirana_voalamina}
                                </div>
                                <div class="daty-naoty">
                                    <i class="fas fa-clock"></i>
                                    Farany: ${asa_iray.daty_farany_voalamina}
                                </div>
                            </div>
                            <div class="asa-naoty">
                                ${asa_iray.rakitra ? `
                                    <button class="btn btn-secondary btn-sm" onclick="aseho_rakitra('${afeno_html(asa_iray.rakitra)}')" title="Jereo ny rakitra">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                ` : ''}
                                <button class="bokotra-fafana" onclick="aseho_modal_fafana(${asa_iray.id}, '${afeno_html(asa_iray.lohateny)}', '${asa_iray.daty_fampidirana_voalamina}')" title="Fafao ity asa ity">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    fitoerana.innerHTML = html;
}

// Fonction haka toe javatra asa
function haka_toe_javatra_asa(tapitra) {
    if (tapitra) {
        return {
            toe: 'tapitra',
            soratra: 'Tapitra',
            kilasy: 'poor',
            kisary: 'fas fa-times-circle'
        };
    } else {
        return {
            toe: 'misokatra',
            soratra: 'Mbola misokatra',
            kilasy: 'good',
            kisary: 'fas fa-clock'
        };
    }
}

// Fonction afeno HTML
function afeno_html(soratra) {
    const div = document.createElement('div');
    div.textContent = soratra;
    return div.innerHTML;
}

// Fonction aseho modal fafana
function aseho_modal_fafana(id_asa, lohateny, daty_fampidirana) {
    asa_hofafana = id_asa;
    
    const modal = document.getElementById('modal-fafana');
    const antsipiriany_asa = document.getElementById('antsipiriany-asa');
    
    antsipiriany_asa.innerHTML = `
        <p><strong>Lohateny:</strong> ${afeno_html(lohateny)}</p>
        <p><strong>Daty nalefa:</strong> ${daty_fampidirana}</p>
        <p><strong>Kilasy:</strong> ${kilasy_ankehitriny}</p>
    `;
    
    modal.style.display = 'flex';
}

// Fonction hidio modal fafana
function hidio_modal_fafana() {
    const modal = document.getElementById('modal-fafana');
    modal.style.display = 'none';
    asa_hofafana = null;
}

// Fonction hamarino fafana
function hamarino_fafana() {
    if (!asa_hofafana) {
        aseho_fampandrenesana('Fahadisoana: Tsy misy asa voasafidy hofafana', 'hadisoana');
        return;
    }
    
    // Mamorona rakitra PHP ho an'ny fafana
    fetch('fafao_asa.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id_asa=${asa_hofafana}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.fahombiazana) {
            aseho_fampandrenesana('Voafafa soa aman-tsara ny asa', 'fahombiazana');
            hidio_modal_fafana();
            // Avereno ampidirina ny antsipiriany kilasy
            ampidiro_antsipiriany_kilasy(kilasy_ankehitriny);
        } else {
            aseho_fampandrenesana('Fahadisoana tamin\'ny fafana: ' + data.hadisoana, 'hadisoana');
        }
    })
    .catch(error => {
        aseho_fampandrenesana('Fahadisoana fifandraisana tamin\'ny fafana', 'hadisoana');
        console.error('Fahadisoana:', error);
    });
}

// Fonction aseho rakitra
function aseho_rakitra(anarana_rakitra) {
    const modal = document.getElementById('modal-rakitra');
    const votoaty = document.getElementById('votoaty-rakitra');
    
    const lalana_rakitra = `rakitra_asa/${anarana_rakitra}`;
    const extension = anarana_rakitra.split('.').pop().toLowerCase();
    
    let html = '';
    
    if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
        html = `<img src="${lalana_rakitra}" alt="Sary" style="max-width: 100%; height: auto;">`;
    } else if (extension === 'pdf') {
        html = `
            <embed src="${lalana_rakitra}" type="application/pdf" width="100%" height="500px">
            <p style="margin-top: 1rem;">
                <a href="${lalana_rakitra}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-external-link-alt"></i>
                    Sokafy amin'ny varavarankely vaovao
                </a>
            </p>
        `;
    } else {
        html = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-file" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                <p><strong>Rakitra:</strong> ${afeno_html(anarana_rakitra)}</p>
                <a href="${lalana_rakitra}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download"></i>
                    Alao
                </a>
            </div>
        `;
    }
    
    votoaty.innerHTML = html;
    modal.style.display = 'flex';
}

// Fonction hidio modal rakitra
function hidio_modal_rakitra() {
    const modal = document.getElementById('modal-rakitra');
    modal.style.display = 'none';
}

// Fonction hiverina amin'ny lisitry kilasy
function aseho_lisitry_kilasy() {
    document.getElementById('seho-antsipiriany-kilasy').style.display = 'none';
    document.getElementById('seho-kilasy').style.display = 'block';
    kilasy_ankehitriny = '';
}

// Fonction aseho fampandrenesana
function aseho_fampandrenesana(hafatra, karazana = 'fampahalalana') {
    // Fafao ny fampandrenesana efa misy
    const fampandrenesana_efa_misy = document.querySelectorAll('.fampandrenesana');
    fampandrenesana_efa_misy.forEach(fampandrenesana => fampandrenesana.remove());
    
    const fampandrenesana = document.createElement('div');
    fampandrenesana.className = `fampandrenesana fampandrenesana-${karazana}`;
    fampandrenesana.innerHTML = `
        <div class="votoaty-fampandrenesana">
            <i class="fas fa-${haka_sary_fampandrenesana(karazana)}"></i>
            <span>${hafatra}</span>
        </div>
        <button class="hidio-fampandrenesana" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Styles ho an'ny fampandrenesana
    fampandrenesana.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${haka_loko_fampandrenesana(karazana)};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        z-index: 1001;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-width: 300px;
        animation: slideInRight 0.3s ease;
    `;

    document.body.appendChild(fampandrenesana);

    // Fafana mandeha ho azy aorian'ny 5 segondra
    setTimeout(() => {
        if (fampandrenesana.parentElement) {
            fampandrenesana.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => fampandrenesana.remove(), 300);
        }
    }, 5000);
}

function haka_sary_fampandrenesana(karazana) {
    const sary = {
        fahombiazana: 'check-circle',
        hadisoana: 'exclamation-circle',
        fampitandremana: 'exclamation-triangle',
        fampahalalana: 'info-circle'
    };
    return sary[karazana] || 'info-circle';
}

function haka_loko_fampandrenesana(karazana) {
    const loko = {
        fahombiazana: '#22c55e',
        hadisoana: '#ef4444',
        fampitandremana: '#f59e0b',
        fampahalalana: '#3b82f6'
    };
    return loko[karazana] || '#3b82f6';
}

// Hidio ny modal raha tsindrina ny ivelany
document.addEventListener('click', function(event) {
    const modal_fafana = document.getElementById('modal-fafana');
    const modal_rakitra = document.getElementById('modal-rakitra');
    
    if (event.target === modal_fafana) {
        hidio_modal_fafana();
    }
    
    if (event.target === modal_rakitra) {
        hidio_modal_rakitra();
    }
});

// Hidio ny modal amin'ny kitendry Échap
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hidio_modal_fafana();
        hidio_modal_rakitra();
    }
});

// Animation CSS ho an'ny spinner
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .btn-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        margin-right: 0.5rem;
    }
    
    .mb-2 {
        margin-bottom: 1rem;
    }
`;
document.head.appendChild(style);