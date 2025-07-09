// Fari-pitsinjovana ankapobe
let kilasy_ankehitriny = '';
let naoty_hofafana = null;

// Fonction ampidirana antsipiriany kilasy
function ampidiro_antsipiriany_kilasy(anarana_kilasy) {
    kilasy_ankehitriny = anarana_kilasy;
    
    // Aseho ny famantarana fampidirana
    aseho_toe_fampidirana();
    
    // Atao ny antso AJAX haka ny naoty
    fetch(`haka_naoty_kilasy.php?kilasy=${encodeURIComponent(anarana_kilasy)}`)
        .then(response => response.json())
        .then(data => {
            if (data.fahombiazana) {
                aseho_antsipiriany_kilasy(anarana_kilasy, data.naoty, data.statistika);
            } else {
                aseho_fampandrenesana('Fahadisoana tamin\'ny fampidirana ny naoty: ' + data.hadisoana, 'hadisoana');
                console.error('Fahadisoana:', data.hadisoana);
            }
        })
        .catch(error => {
            aseho_fampandrenesana('Fahadisoana fifandraisana tamin\'ny fampidirana ny naoty', 'hadisoana');
            console.error('Fahadisoana:', error);
        })
        .finally(() => {
            afeno_toe_fampidirana();
        });
}

// Fonction aseho toe fampidirana
function aseho_toe_fampidirana() {
    const fitoerana = document.getElementById('fitoerana-mpianatra');
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
                <p style="color: #6b7280;">Mampiditra ny naoty...</p>
            </div>
        `;
    }
}

// Fonction afeno toe fampidirana
function afeno_toe_fampidirana() {
    // Ny toe fampidirana dia hosoloina ny votoaty tena izy
}

// Fonction aseho antsipiriany kilasy
function aseho_antsipiriany_kilasy(anarana_kilasy, naoty, statistika) {
    // Afeno ny seho kilasy ary aseho ny seho antsipiriany
    document.getElementById('seho-kilasy').style.display = 'none';
    document.getElementById('seho-antsipiriany-kilasy').style.display = 'block';
    
    // Havaozina ny lohateny sy ny statistika
    document.getElementById('anarana-kilasy').textContent = anarana_kilasy;
    document.getElementById('isan-mpianatra-kilasy').textContent = `${statistika.isan_mpianatra} mpianatra`;
    document.getElementById('salan-isa-kilasy').textContent = `Salan-isa: ${statistika.salan_isa}/20`;
    
    // Alamino ny naoty araka ny mpianatra
    const naoty_mpianatra = alamino_naoty_araka_mpianatra(naoty);
    
    // Aseho ny mpianatra sy ny naoty
    aseho_mpianatra(naoty_mpianatra);
}

// Fonction alamino naoty araka mpianatra
function alamino_naoty_araka_mpianatra(naoty) {
    const naoty_mpianatra = {};
    
    naoty.forEach(naoty_iray => {
        if (!naoty_mpianatra[naoty_iray.anarana_mpianatra]) {
            naoty_mpianatra[naoty_iray.anarana_mpianatra] = {
                anarana_mpampiasa: naoty_iray.anarana_mpianatra,
                naoty: [],
                salan_isa: 0
            };
        }
        naoty_mpianatra[naoty_iray.anarana_mpianatra].naoty.push(naoty_iray);
    });
    
    // Kajy ny salan-isa ho an'ny mpianatra tsirairay
    Object.keys(naoty_mpianatra).forEach(anarana_mpampiasa => {
        const mpianatra = naoty_mpianatra[anarana_mpampiasa];
        if (mpianatra.naoty.length > 0) {
            const totalina = mpianatra.naoty.reduce((isa, naoty_iray) => isa + parseFloat(naoty_iray.naoty), 0);
            mpianatra.salan_isa = (totalina / mpianatra.naoty.length).toFixed(1);
        }
    });
    
    return naoty_mpianatra;
}

// Fonction aseho mpianatra
function aseho_mpianatra(naoty_mpianatra) {
    const fitoerana = document.getElementById('fitoerana-mpianatra');
    
    if (Object.keys(naoty_mpianatra).length === 0) {
        fitoerana.innerHTML = `
            <div class="karatra">
                <div class="empty-state">
                    <i class="fas fa-user-graduate" style="font-size: 3rem; margin-bottom: 1rem; color: #9ca3af;"></i>
                    <p>Tsy misy naoty hita ho an'ity kilasy ity.</p>
                </div>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    Object.keys(naoty_mpianatra).sort().forEach(anarana_mpampiasa => {
        const mpianatra = naoty_mpianatra[anarana_mpampiasa];
        const kilasy_salan_isa = haka_kilasy_naoty(mpianatra.salan_isa);
        
        html += `
            <div class="karatra karatra-mpianatra">
                <div class="lohateny-karatra">
                    <div class="fampahalalana-mpianatra">
                        <h3>${afeno_html(mpianatra.anarana_mpampiasa)}</h3>
                        <p class="anarana-mpampiasa">@${afeno_html(mpianatra.anarana_mpampiasa)}</p>
                    </div>
                    <div class="salan-isa-mpianatra ${kilasy_salan_isa}">
                        Salan-isa: ${mpianatra.salan_isa}/20
                    </div>
                </div>
                <div class="votoaty-karatra">
                    <div class="lisitry-naoty">
                        ${mpianatra.naoty.map(naoty_iray => `
                            <div class="singa-naoty">
                                <div class="fampahalalana-naoty">
                                    <div class="taranja-naoty">${afeno_html(naoty_iray.taranja)}</div>
                                    <div class="daty-naoty">
                                        <i class="fas fa-calendar"></i>
                                        ${naoty_iray.daty_voalamina}
                                    </div>
                                </div>
                                <div class="asa-naoty">
                                    <div class="sanda-naoty ${haka_kilasy_naoty(naoty_iray.naoty)}">
                                        ${naoty_iray.naoty}/20
                                    </div>
                                    <button class="bokotra-fafana" onclick="aseho_modal_fafana(${naoty_iray.id}, '${afeno_html(naoty_iray.anarana_mpianatra)}', '${naoty_iray.naoty}', '${naoty_iray.daty_voalamina}')" title="Fafao ity naoty ity">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    });
    
    fitoerana.innerHTML = html;
}

// Fonction haka kilasy naoty
function haka_kilasy_naoty(naoty) {
    const naoty_isa = parseFloat(naoty);
    if (naoty_isa >= 16) return 'excellent';
    if (naoty_isa >= 14) return 'good';
    if (naoty_isa >= 10) return 'average';
    return 'poor';
}

// Fonction afeno HTML
function afeno_html(soratra) {
    const div = document.createElement('div');
    div.textContent = soratra;
    return div.innerHTML;
}

// Fonction aseho modal fafana
function aseho_modal_fafana(id_naoty, anarana_mpianatra, sanda_naoty, daty_naoty) {
    naoty_hofafana = id_naoty;
    
    const modal = document.getElementById('modal-fafana');
    const antsipiriany_naoty = document.getElementById('antsipiriany-naoty');
    
    antsipiriany_naoty.innerHTML = `
        <p><strong>Mpianatra:</strong> ${afeno_html(anarana_mpianatra)}</p>
        <p><strong>Naoty:</strong> ${sanda_naoty}/20</p>
        <p><strong>Daty:</strong> ${daty_naoty}</p>
    `;
    
    modal.style.display = 'flex';
}

// Fonction hidio modal fafana
function hidio_modal_fafana() {
    const modal = document.getElementById('modal-fafana');
    modal.style.display = 'none';
    naoty_hofafana = null;
}

// Fonction hamarino fafana
function hamarino_fafana() {
    if (!naoty_hofafana) {
        aseho_fampandrenesana('Fahadisoana: Tsy misy naoty voasafidy hofafana', 'hadisoana');
        return;
    }
    
    // Mamorona rakitra PHP ho an'ny fafana
    fetch('fafao_naoty.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id_naoty=${naoty_hofafana}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.fahombiazana) {
            aseho_fampandrenesana('Voafafa soa aman-tsara ny naoty', 'fahombiazana');
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
    const modal = document.getElementById('modal-fafana');
    if (event.target === modal) {
        hidio_modal_fafana();
    }
});

// Hidio ny modal amin'ny kitendry Échap
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hidio_modal_fafana();
    }
});