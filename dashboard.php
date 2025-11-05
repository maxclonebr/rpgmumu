<?php
session_start();
include "db.php";
include "check_ban.php"; // Protege a página

if (!isset($_SESSION['PlayerID'])) {
    die("Acesso negado. Faça login.");
}

$playerID = $_SESSION['PlayerID'];

// Dados do jogador
$stmtPlayer = sqlsrv_query($conn, "SELECT TOP 1 * FROM Players p
    LEFT JOIN BankAccounts b ON p.PlayerID = b.PlayerID
    WHERE p.PlayerID=?", [$playerID]);
$player = ($stmtPlayer && $row = sqlsrv_fetch_array($stmtPlayer, SQLSRV_FETCH_ASSOC)) ? $row : [];
$pix = $player['Pix'] ?? 0;

// Personagens
$personagens = [];
$sql = "SELECT TOP 1000 [CharID],[PlayerID],[Name],[Class],[Level],[Exp],[HP],[Mana],[MaxHP],[MaxMana],[Power]
        FROM Characters WHERE PlayerID=?";
$stmtChar = sqlsrv_query($conn, $sql, [$playerID]);
if ($stmtChar && sqlsrv_has_rows($stmtChar)) {
    while ($row = sqlsrv_fetch_array($stmtChar, SQLSRV_FETCH_ASSOC)) {
        $personagens[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Dashboard - Mumu RPG Futurista</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Orbitron:wght@500&display=swap" rel="stylesheet">
<style> body { background: #0a0a0a; color: #fff; font-family: 'Orbitron', 'Roboto', sans-serif; margin: 0; padding: 20px; } .container { max-width: 1200px; margin: auto; padding: 20px; } .top-bar { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-bottom: 20px; } .top-bar a, .top-bar button { padding: 10px 20px; border-radius: 8px; text-decoration: none; color: #fff; background: #111; border: 1px solid #0ff; box-shadow: 0 0 10px #0ff50f, 0 0 20px #0ff50f inset; transition: 0.3s; cursor: pointer; } .top-bar a:hover, .top-bar button:hover { background: #0ff; color: #000; box-shadow: 0 0 25px #0ff, 0 0 40px #00ffff inset; } .jogador-info { background: #111; color: #0ff; padding: 20px; border-radius: 15px; margin: 0 auto 20px auto; text-align: center; max-width: 800px; box-shadow: 0 0 20px #0ff55a, 0 0 30px #00ffff inset; } .jogador-info h1 { margin-top: 0; font-size: 2em; text-shadow: 0 0 10px #0ff, 0 0 20px #00ffff; } .jogador-info p { margin: 5px 0; font-weight: bold; } .status-panel-future { background: linear-gradient(145deg, #0c0c1a, #11112b); border: 2px solid #0ff; border-radius: 25px; box-shadow: 0 0 20px rgba(0, 255, 255, 0.3); padding: 25px; color: #e0f7ff; font-family: 'Orbitron', sans-serif; width: 350px; margin: 20px auto; text-align: center; transition: 0.3s; } .status-panel-future:hover { box-shadow: 0 0 35px #0ff, 0 0 60px #00ffff inset; } .status-title { color: #0ff; margin-bottom: 10px; text-shadow: 0 0 12px #0ff, 0 0 25px #00ffff; font-size: 1.3em; } .character-name { font-size: 1.7em; color: #ffcc00; margin-bottom: 15px; font-weight: bold; text-shadow: 0 0 10px #ffcc00, 0 0 20px #ffaa00; } .status-info { display: flex; justify-content: space-around; margin-bottom: 15px; } .status-bar { margin: 10px 0; } .label { text-align: left; font-size: 0.9rem; color: #7efaff; margin-bottom: 4px; text-shadow: 0 0 4px #0ff; } .bar-container { position: relative; background: rgba(0, 255, 255, 0.05); border: 1px solid #0ff; border-radius: 12px; height: 20px; overflow: hidden; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 5px #0ff inset; } .bar { height: 100%; border-radius: 12px; transition: width 0.5s ease-in-out, box-shadow 0.3s; text-align: center; } .bar.hp { background: linear-gradient(90deg, #ff0044, #ff6688); box-shadow: 0 0 12px #ff0044; } .bar.mana { background: linear-gradient(90deg, #0044ff, #66aaff); box-shadow: 0 0 12px #0044ff; } .bar.xp { background: linear-gradient(90deg, #ffcc00, #ffee66); box-shadow: 0 0 12px #ffcc00; } .bar.power { background: linear-gradient(90deg, #00ffaa, #00ffff); box-shadow: 0 0 12px #00ffaa; } .bar-text { position: absolute; width: 100%; text-align: center; font-size: 0.85rem; color: #fff; pointer-events: none; text-shadow: 0 0 5px #0ff; } .btn-futurista { display: block; margin: 15px auto; padding: 12px 25px; background: linear-gradient(135deg, #0ff, #00f); border: none; border-radius: 20px; font-weight: bold; color: #000; cursor: pointer; box-shadow: 0 0 25px #0ff, 0 0 50px #00ffff inset; transition: transform 0.2s, box-shadow 0.2s; } .btn-futurista:hover { transform: scale(1.08); box-shadow: 0 0 35px #0ff, 0 0 70px #00ffff inset; } .popup-msg { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 20px; font-weight: bold; color: #fff; background: linear-gradient(135deg, #00f, #0ff); box-shadow: 0 0 25px rgba(0, 255, 255, 0.6); z-index: 9999; animation: fadeInOut 3s forwards; display: none; } @keyframes fadeInOut { 0% { opacity:0; transform:translateY(-20px); } 10%, 90% { opacity:1; transform:translateY(0); } 100% { opacity:0; transform:translateY(-20px); } } @media(max-width:768px){ .status-panel-future { width:90%; margin:auto; } .top-bar { justify-content: space-around; } } </style>
</head>
<body>
<div class="container">
    <nav class="top-bar">
        <a href="logout.php">🚪 Sair</a>
        <a href="map001.php">🚪 MAPA </a>
        <a href="personagens.php">👤 Personagens</a>
        <a href="bank.php">🏦 Banco 💰</a>
        <a href="start_dungeon.php">🗡️ Masmorra</a>
        <a href="inventario.php">🎒 Inventário</a>
        <a href="get_mochila.php">🎒 Mochila</a>
        <a href="inventario_loja.php">🎒 Loja</a>
        <a href="saude.php">🩺 Saúde</a>
        <a href="mercado.php">🛒 Mercado</a>
        <a href="bolsa.php">📈 Bolsa</a>
        <a href="trade.php">📈 trade</a>
        <a href="fazenda.php">🌾 Fazenda</a>
        <a href="del_conta.php" onclick="return confirm('Tem certeza?');">🗑️ Bloquear Conta</a>
    </nav>

    <div class="jogador-info">
        <p>🏰 Painel Visual do Mumu RPG Futurista</p>
        <p>Bem-vindo, <strong><?= htmlspecialchars($player['Username'] ?? 'Jogador') ?></strong>!</p>
        <p>💰 Pix: <strong><?= $pix ?></strong></p>
        <p><strong>Último login:</strong> <?= !empty($player['LastLoginTime']) ? $player['LastLoginTime']->format("d/m/Y H:i") : "Nunca" ?></p>
        <p><strong>IP logado:</strong> <?= !empty($player['LastLoginIP']) ? htmlspecialchars($player['LastLoginIP']) : "Desconhecido" ?></p>
    </div>

    <h2 style="text-align:center; color:#0ff; margin-bottom:10px;">⚡ Status dos Personagens</h2>

    <?php if (!empty($personagens)): ?>
    <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:15px;">
        <?php foreach ($personagens as $p):
            $hpNow     = (int)($p['HP'] ?? 0);
            $hpMax     = (int)($p['MaxHP'] ?? 1000);
            $manaNow   = (int)($p['Mana'] ?? 0);
            $manaMax   = (int)($p['MaxMana'] ?? 500);
            $xpNow     = (int)($p['Exp'] ?? 0);
            $xpMax     = 1000;
            $powerNow  = (int)($p['Power'] ?? 0);
            $charId    = htmlspecialchars($p['CharID'] ?? 0);
            $class     = htmlspecialchars($p['Class'] ?? 'Desconhecida');
            $level     = htmlspecialchars($p['Level'] ?? 0);
            $name      = htmlspecialchars($p['Name'] ?? 'Sem Nome');
            $hpPerc    = $hpMax > 0 ? round($hpNow / $hpMax * 100) : 0;
            $manaPerc  = $manaMax > 0 ? round($manaNow / $manaMax * 100) : 0;
            $xpPerc    = $xpMax > 0 ? round($xpNow / $xpMax * 100) : 0;
            $powerPerc = min(100, max(0, $powerNow));
        ?>
        <div class="status-panel-future">
            <h3 class="status-title">🧬 Status do Personagem</h3>
            <div class="character-name">👤 <?= $name ?></div>

            <div class="status-info">
                <div><strong>Classe:</strong> <?= $class ?></div>
                <div><strong>Level:</strong> <span id="level-<?= $charId ?>"><?= $level ?></span></div>
            </div>

            <div class="status-bar">
                <div class="label">⚡ Power</div>
                <div class="bar-container">
                    <div id="power-<?= $charId ?>" class="bar power" style="width:<?= $powerPerc ?>%;"></div>
                    <span class="bar-text"><?= $powerNow ?> / 100 (<?= $powerPerc ?>%)</span>
                </div>
            </div>

            <div class="status-bar">
                <div class="label">❤️ HP</div>
                <div class="bar-container">
                    <div id="hp-<?= $charId ?>" class="bar hp" style="width:<?= $hpPerc ?>%;"></div>
                    <span class="bar-text"><?= $hpNow ?> / <?= $hpMax ?></span>
                </div>
            </div>

            <div class="status-bar">
                <div class="label">🔷 Mana</div>
                <div class="bar-container">
                    <div id="mana-<?= $charId ?>" class="bar mana" style="width:<?= $manaPerc ?>%;"></div>
                    <span class="bar-text"><?= $manaNow ?> / <?= $manaMax ?></span>
                </div>
            </div>

            <div class="status-bar">
                <div class="label">💠 XP</div>
                <div class="bar-container">
                    <div id="xp-<?= $charId ?>" class="bar xp" style="width:<?= $xpPerc ?>%;"></div>
                    <span class="bar-text"><?= $xpNow ?> / <?= $xpMax ?></span>
                </div>
            </div>


<?php
$lastRestore = $p['LastRestore'] ?? null;
$cooldown = 300;
$remaining = 0;
if($lastRestore){
    $lastTime = $lastRestore instanceof DateTime ? $lastRestore : new DateTime($lastRestore);
    $diff = (new DateTime())->getTimestamp() - $lastTime->getTimestamp();
    $remaining = max(0, $cooldown - $diff);
}
?>
<button class="btn-futurista" id="btn-restore-<?= $charId ?>" data-charid="<?= $charId ?>" data-cooldown="<?= $remaining ?>">
    Restaurar (500 Pix)
</button>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p style="text-align:center;">Nenhum personagem encontrado. <a href="criar_personagem.php">Crie um agora!</a></p>
    <?php endif; ?>
</div>

<div id="popupMsg" class="popup-msg"></div>


<script>
function atualizarCooldown(btn){
    let cooldown = parseInt(btn.dataset.cooldown);
    if(cooldown > 0){
        btn.disabled = true;
        btn.innerText = `Aguarde ${cooldown}s`;
        const interval = setInterval(() => {
            cooldown--;
            btn.dataset.cooldown = cooldown;
            if(cooldown <= 0){
                btn.disabled = false;
                btn.innerText = `Restaurar (500 Pix)`;
                clearInterval(interval);
            } else {
                btn.innerText = `Aguarde ${cooldown}s`;
            }
        }, 1000);
    }
}

document.querySelectorAll('.btn-futurista').forEach(btn => {
    atualizarCooldown(btn);
    btn.addEventListener('click', () => {
        const charID = btn.dataset.charid;
        fetch('ajax_restaurar.php', {
            method: 'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({CharID: charID})
        })
        .then(res => res.json())
        .then(data => {
            const popup = document.getElementById('popupMsg');
            popup.innerText = data.message;
            popup.style.display = 'block';
            setTimeout(()=>{popup.style.display='none';},3000);

            if(data.success){
                // Atualiza barras e level
                if(data.Power!==undefined){
                    const bar = document.querySelector(`#power-${charID}`);
                    const text = bar.nextElementSibling;
                    const perc = Math.min(100, Math.round(data.Power));
                    bar.style.width = perc+'%';
                    text.innerText = `${data.Power} / 100 (${perc}%)`;
                }
                if(data.HP!==undefined && data.MaxHP!==undefined){
                    const bar = document.querySelector(`#hp-${charID}`);
                    const text = bar.nextElementSibling;
                    const perc = Math.round(data.HP/data.MaxHP*100);
                    bar.style.width = perc+'%';
                    text.innerText = `${data.HP} / ${data.MaxHP}`;
                }
                if(data.Mana!==undefined && data.MaxMana!==undefined){
                    const bar = document.querySelector(`#mana-${charID}`);
                    const text = bar.nextElementSibling;
                    const perc = Math.round(data.Mana/data.MaxMana*100);
                    bar.style.width = perc+'%';
                    text.innerText = `${data.Mana} / ${data.MaxMana}`;
                }
                if(data.Exp!==undefined){
                    const bar = document.querySelector(`#xp-${charID}`);
                    const text = bar.nextElementSibling;
                    const xpMax = 1000;
                    const perc = Math.round(data.Exp/xpMax*100);
                    bar.style.width = perc+'%';
                    text.innerText = `${data.Exp} / ${xpMax}`;
                }
                if(data.Level!==undefined){
                    document.querySelector(`#level-${charID}`).innerText = data.Level;
                }
                if(data.Pix!==undefined){
                    document.querySelector('.jogador-info strong').innerText = data.Pix;
                }

                // Inicia cooldown de 5 minutos
                btn.dataset.cooldown = 300;
                atualizarCooldown(btn);
            }
        })
        .catch(()=>alert("Erro de conexão!"));
    });
});
</script>



<script>
function restaurarPersonagem(charID){
    fetch('ajax_restaurar.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ CharID: charID })
    })
    .then(res => res.json())
    .then(data => {
        const popup = document.getElementById('popupMsg');
        popup.innerText = data.message;
        popup.style.display = 'block';
        setTimeout(() => { popup.style.display = 'none'; }, 3000);

        if(data.success){
            // Atualiza Power
            if(data.Power !== undefined){
                const bar = document.querySelector(`#power-${charID}`);
                const text = bar.nextElementSibling;
                const perc = Math.min(100, Math.round(data.Power));
                bar.style.width = perc + '%';
                text.innerText = `${data.Power} / 100 (${perc}%)`;
            }
            // Atualiza HP
            if(data.HP !== undefined && data.MaxHP !== undefined){
                const bar = document.querySelector(`#hp-${charID}`);
                const text = bar.nextElementSibling;
                const perc = Math.round(data.HP / data.MaxHP * 100);
                bar.style.width = perc + '%';
                text.innerText = `${data.HP} / ${data.MaxHP}`;
            }
            // Atualiza Mana
            if(data.Mana !== undefined && data.MaxMana !== undefined){
                const bar = document.querySelector(`#mana-${charID}`);
                const text = bar.nextElementSibling;
                const perc = Math.round(data.Mana / data.MaxMana * 100);
                bar.style.width = perc + '%';
                text.innerText = `${data.Mana} / ${data.MaxMana}`;
            }
            // Atualiza XP
            if(data.Exp !== undefined){
                const bar = document.querySelector(`#xp-${charID}`);
                const text = bar.nextElementSibling;
                const xpMax = 1000;
                const perc = Math.round(data.Exp / xpMax * 100);
                bar.style.width = perc + '%';
                text.innerText = `${data.Exp} / ${xpMax}`;
            }
            // Atualiza Level
            if(data.Level !== undefined){
                document.querySelector(`#level-${charID}`).innerText = data.Level;
            }
            // Atualiza Pix
            if(data.Pix !== undefined){
                document.querySelector('.jogador-info strong').innerText = data.Pix;
            }
        }
    })
    .catch(() => alert("Erro de conexão!"));
}
</script>
</body>
</html>
