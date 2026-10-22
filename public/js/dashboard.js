const {
    season: selectedSeason,
    openDailyReport: shouldOpenDailyReport,
    matchCutoffHour: matchCutoffHour,
    routes: sf2Routes
} = window.SF2;

function clearDailyReportQuery() {
    const url = new URL(window.location.href);

    if (!url.searchParams.has("report_fecha")) {
        return;
    }

    url.searchParams.delete("report_fecha");

    if (selectedSeason) {
        url.searchParams.set("season", selectedSeason);
    }

    history.replaceState({}, document.title, `${url.pathname}${url.search}${url.hash}`);
}

const quickMatchForm = document.getElementById("quickMatchForm");
const quickModal = document.getElementById("modalPleitoMovil");
const quickSetupStep = document.getElementById("quickSetupStep");
const quickCounterStep = document.getElementById("quickCounterStep");
const quickMatchFooter = document.getElementById("quickMatchFooter");
const quickStartButton = document.getElementById("quickStartButton");
const quickEditButton = document.getElementById("quickEditButton");
const quickPlayer1 = document.getElementById("quickPlayer1");
const quickPlayer2 = document.getElementById("quickPlayer2");
const quickScore1 = document.getElementById("quickScore1");
const quickScore2 = document.getElementById("quickScore2");
const quickPlayerName1 = document.getElementById("quickPlayerName1");
const quickPlayerName2 = document.getElementById("quickPlayerName2");
const quickPlayerPanel1 = document.getElementById("quickPlayerPanel1");
const quickPlayerPanel2 = document.getElementById("quickPlayerPanel2");
const quickSetupMessage = document.getElementById("quickSetupMessage");
const quickMessage = document.getElementById("quickMatchMessage");
const quickMatchIntro = document.getElementById("quickMatchIntro");
const quickShareActions = document.getElementById("quickShareActions");
const quickShareImageButton = document.getElementById("quickShareImageButton");
const quickPrintButton = document.getElementById("quickPrintButton");
const dailyReportShareButton = document.getElementById("dailyReportShareButton");
const dailyReportImageButton = document.getElementById("dailyReportImageButton");
const dailyReportPrintButton = document.getElementById("dailyReportPrintButton");
const quickSaveButton = document.getElementById("quickSaveButton");
const quickGanadorId = document.getElementById("quickGanadorId");
const quickPerdedorId = document.getElementById("quickPerdedorId");
const quickGanadorImageId = document.getElementById("quickGanadorImageId");
const quickPerdedorImageId = document.getElementById("quickPerdedorImageId");

function getQuickImage(slot) {
    return document.querySelector(`input[name='quick_imagen_${slot}']:checked`)?.value || "";
}
const quickVictoriasGanador = document.getElementById("quickVictoriasGanador");
const quickVictoriasPerdedor = document.getElementById("quickVictoriasPerdedor");
const quickMatchScores = {
    1: 0,
    2: 0
};
let quickMatchStarted = false;

function getQuickTarget() {
    return parseInt(document.querySelector("input[name='formato_pleito']:checked").value, 10);
}

function getSelectedPlayerName(select, defaultName) {
    return select.value ? select.options[select.selectedIndex].text : defaultName;
}

function getSelectedPlayerRanking(select) {
    return select.value ? select.options[select.selectedIndex].dataset.ranking : "-";
}

function setAlertMessage(element, message, type) {
    element.textContent = message;
    element.classList.remove("alert-info", "alert-warning", "alert-danger", "alert-success");
    element.classList.add(`alert-${type}`);
}

function setQuickMessage(message, type) {
    setAlertMessage(quickMessage, message, type);
}

function setQuickSetupMessage(message, type) {
    setAlertMessage(quickSetupMessage, message, type);
}

function clearQuickHiddenFields() {
    quickGanadorId.value = "";
    quickPerdedorId.value = "";
    quickGanadorImageId.value = "";
    quickPerdedorImageId.value = "";
    quickVictoriasGanador.value = "";
    quickVictoriasPerdedor.value = "";
}

function getQuickWinner() {
    const target = getQuickTarget();

    if (quickMatchScores[1] === target && quickMatchScores[2] < target) {
        return 1;
    }

    if (quickMatchScores[2] === target && quickMatchScores[1] < target) {
        return 2;
    }

    return 0;
}

function resetQuickScores() {
    quickMatchScores[1] = 0;
    quickMatchScores[2] = 0;
}

function isEarlyMorningDate(value) {
    const hour = value ? parseInt(value.slice(11, 13), 10) : NaN;

    return !Number.isNaN(hour) && hour < matchCutoffHour;
}

const hourWarningModal = new bootstrap.Modal(document.getElementById("modalAvisoHora"));
const hourWarningValue = document.getElementById("avisoHoraValor");
let hourWarningCallback = null;

// Si la hora es de madrugada avisa para validarla; si no, continua directo.
function confirmEarlyMorning(value, onContinue) {
    if (!isEarlyMorningDate(value)) {
        onContinue();

        return;
    }

    hourWarningValue.textContent = value.replace("T", " ");
    hourWarningCallback = onContinue;
    hourWarningModal.show();
}

document.getElementById("avisoHoraContinuar").addEventListener("click", () => {
    const callback = hourWarningCallback;
    hourWarningCallback = null;
    hourWarningModal.hide();

    if (callback) {
        callback();
    }
});

function validateQuickSetup() {
    const target = getQuickTarget();

    if (!quickPlayer1.value || !quickPlayer2.value) {
        setQuickSetupMessage(`Selecciona Player 1 y Player 2. Este match es primero a ganar ${target} manos.`, "info");
        return false;
    }

    if (quickPlayer1.value === quickPlayer2.value) {
        setQuickSetupMessage("Player 1 y Player 2 no pueden ser el mismo jugador.", "danger");
        return false;
    }

    setQuickSetupMessage(`Listo. Este match es primero a ganar ${target} manos.`, "success");
    return true;
}

function showQuickSetup() {
    quickMatchStarted = false;
    quickSetupStep.classList.remove("d-none");
    quickCounterStep.classList.add("d-none");
    quickMatchFooter.classList.add("d-none");
    quickShareActions.classList.remove("is-visible");
    resetQuickScores();
    clearQuickHiddenFields();
    updateQuickMatch();
}

function showQuickCounter() {
    const target = getQuickTarget();

    quickMatchStarted = true;
    quickSetupStep.classList.add("d-none");
    quickCounterStep.classList.remove("d-none");
    quickMatchFooter.classList.remove("d-none");
    quickMatchIntro.textContent = `Este match es primero a ganar ${target} manos.`;
    resetQuickScores();
    updateQuickMatch();
}

function getQuickResult() {
    const winner = getQuickWinner();
    const player1Name = getSelectedPlayerName(quickPlayer1, "Player 1");
    const player2Name = getSelectedPlayerName(quickPlayer2, "Player 2");
    const player1Ranking = getSelectedPlayerRanking(quickPlayer1);
    const player2Ranking = getSelectedPlayerRanking(quickPlayer2);

    if (winner === 1) {
        return {
            winnerName: player1Name,
            loserName: player2Name,
            winnerScore: quickMatchScores[1],
            loserScore: quickMatchScores[2],
            winnerRanking: player1Ranking,
            loserRanking: player2Ranking,
            line: `${player1Name} gana ${quickMatchScores[1]} - ${quickMatchScores[2]} contra ${player2Name}.`
        };
    }

    if (winner === 2) {
        return {
            winnerName: player2Name,
            loserName: player1Name,
            winnerScore: quickMatchScores[2],
            loserScore: quickMatchScores[1],
            winnerRanking: player2Ranking,
            loserRanking: player1Ranking,
            line: `${player2Name} gana ${quickMatchScores[2]} - ${quickMatchScores[1]} contra ${player1Name}.`
        };
    }

    return null;
}

function createQuickResultCanvas() {
    const result = getQuickResult();
    const target = getQuickTarget();
    const matchDate = getQuickMatchDateText();
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");

    canvas.width = 900;
    canvas.height = 520;

    ctx.fillStyle = "#f4f7fb";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = "#0d6efd";
    ctx.fillRect(0, 0, canvas.width, 96);
    ctx.fillStyle = "#ffffff";
    ctx.font = "700 42px Arial";
    ctx.textAlign = "center";
    ctx.fillText("Resultado del Match", canvas.width / 2, 44);
    ctx.font = "600 22px Arial";
    ctx.fillText(matchDate, canvas.width / 2, 78);

    ctx.fillStyle = "#111827";
    ctx.font = "700 28px Arial";
    ctx.textAlign = "center";
    ctx.fillText("Ganador", 300, 155);
    ctx.fillText("Perdedor", 600, 155);

    ctx.font = "700 42px Arial";
    ctx.fillText(result.winnerName, 300, 220);
    ctx.fillText(result.loserName, 600, 220);

    ctx.fillStyle = "#4b5563";
    ctx.font = "600 16px Arial";
    ctx.fillText("Ranking", 165, 198);
    ctx.fillText("Ranking", 735, 198);
    ctx.font = "800 22px Arial";
    ctx.fillText(`#${result.winnerRanking}`, 165, 225);
    ctx.fillText(`#${result.loserRanking}`, 735, 225);
    ctx.textAlign = "center";

    ctx.fillStyle = "#0d6efd";
    ctx.font = "800 104px Arial";
    ctx.fillText(result.winnerScore, 300, 335);
    ctx.fillText(result.loserScore, 600, 335);

    ctx.fillStyle = "#4b5563";
    ctx.font = "700 34px Arial";
    ctx.fillText("vs", canvas.width / 2, 300);

    ctx.fillStyle = "#4b5563";
    ctx.font = "500 28px Arial";
    ctx.fillText(`Primero a ganar ${target} manos`, canvas.width / 2, 430);

    return canvas;
}

function downloadBlob(blob, filename) {
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(link.href), 60000);
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function truncateCanvasText(ctx, value, maxWidth) {
    let text = String(value);

    if (ctx.measureText(text).width <= maxWidth) {
        return text;
    }

    while (text.length > 0 && ctx.measureText(`${text}...`).width > maxWidth) {
        text = text.slice(0, -1);
    }

    return `${text}...`;
}

function getTableRows(tableId, expectedCells) {
    return Array.from(document.querySelectorAll(`#${tableId} tbody tr`))
        .map(row => Array.from(row.cells).map(cell => cell.textContent.trim()))
        .filter(cells => cells.length === expectedCells);
}

function getDailyReportData() {
    return {
        date: document.getElementById("reportFecha").value,
        rankings: getTableRows("dailyRankingTable", 6),
        rankingImages: Array.from(document.querySelectorAll("#dailyRankingTable tbody tr"))
            .filter(row => row.cells.length === 6)
            .map(row => row.cells[1].querySelector("img")?.src),
        matches: getTableRows("dailyMatchesTable", 4),
        matchImages: Array.from(document.querySelectorAll("#dailyMatchesTable tbody tr"))
            .filter(row => row.cells.length === 4)
            .map(row => [row.cells[1].querySelector("img")?.src, row.cells[2].querySelector("img")?.src])
    };
}

function loadReportImage(src) {
    if (!src) {
        return Promise.resolve(null);
    }

    return new Promise(resolve => {
        const imageUrl = new URL(src, window.location.href);
        const isLocalUpload = imageUrl.pathname.includes("/uploads/");
        const image = new Image();
        const timeout = setTimeout(() => resolve(null), 5000);
        image.onload = () => {
            clearTimeout(timeout);
            resolve(image);
        };
        image.onerror = () => {
            clearTimeout(timeout);
            resolve(null);
        };
        if (!isLocalUpload && imageUrl.origin !== window.location.origin) {
            image.crossOrigin = "anonymous";
        }
        image.src = isLocalUpload ? `${window.location.origin}${imageUrl.pathname}${imageUrl.search}` : imageUrl.href;
    });
}

function dailyReportBlob() {
    return createDailyReportCanvas().then(canvas => new Promise((resolve, reject) => {
        try {
            canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error("No se pudo crear la imagen.")), "image/jpeg", 0.92);
        } catch (error) {
            reject(error);
        }
    }));
}

let preparedDailyReportBlob = null;

document.getElementById("modalReporteDia").addEventListener("shown.bs.modal", () => {
    dailyReportBlob()
        .then(blob => { preparedDailyReportBlob = blob; })
        .catch(() => { preparedDailyReportBlob = null; });
});

function showDailyReportError() {
    Swal.fire({
        icon: "error",
        title: "No se pudo preparar el reporte",
        text: "Intenta de nuevo o usa PDF / Imprimir."
    });
}

function drawDailyReportTable(ctx, title, headers, rows, y, colWidths, rowImages = [], rowSizes = []) {
    const x = 60;
    const headerHeight = 34;
    const tableWidth = colWidths.reduce((total, width) => total + width, 0);

    ctx.fillStyle = "#111827";
    ctx.font = "800 26px Arial";
    ctx.textAlign = "left";
    ctx.fillText(title, x, y);
    y += 22;

    ctx.fillStyle = "#0d6efd";
    ctx.fillRect(x, y, tableWidth, headerHeight);
    ctx.fillStyle = "#ffffff";
    ctx.font = "700 18px Arial";

    let currentX = x;
    headers.forEach((header, index) => {
        ctx.fillText(header, currentX + 12, y + 23);
        currentX += colWidths[index];
    });

    y += headerHeight;

    if (rows.length === 0) {
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(x, y, tableWidth, headerHeight);
        ctx.fillStyle = "#4b5563";
        ctx.font = "600 18px Arial";
        ctx.fillText("Sin datos para este dia", x + 12, y + 23);
        return y + headerHeight + 32;
    }

    rows.forEach((row, rowIndex) => {
        const rowHeight = rowSizes[rowIndex] || (rowImages.length ? 40 : 34);
        ctx.fillStyle = rowSizes.length && rowIndex === 0 ? "#fff4ce" : rowSizes.length && rowIndex === 1 ? "#edf2f8" : rowSizes.length && rowIndex === 2 ? "#f8eee6" : rowIndex % 2 === 0 ? "#ffffff" : "#eef4fb";
        ctx.fillRect(x, y, tableWidth, rowHeight);
        ctx.fillStyle = "#111827";
        ctx.font = "600 18px Arial";

        currentX = x;
        row.forEach((cell, index) => {
            const image = rowImages[rowIndex]?.[index];
            const imageSize = rowSizes.length ? ([76, 60, 50][rowIndex] || 26) : 26;
            const textOffset = image ? imageSize + 22 : 12;

            if (image) {
                const imageX = currentX + 10;
                const imageY = y + (rowHeight - imageSize) / 2;
                const cropSize = Math.min(image.naturalWidth, image.naturalHeight);
                ctx.save();
                ctx.beginPath();
                ctx.arc(imageX + imageSize / 2, y + rowHeight / 2, imageSize / 2, 0, Math.PI * 2);
                ctx.clip();
                ctx.drawImage(image, (image.naturalWidth - cropSize) / 2, (image.naturalHeight - cropSize) / 2, cropSize, cropSize, imageX, imageY, imageSize, imageSize);
                ctx.restore();
                if (rowSizes.length && rowIndex < 3) {
                    ctx.strokeStyle = ["#a67614", "#718096", "#9c674b"][rowIndex];
                    ctx.lineWidth = 3;
                    ctx.beginPath();
                    ctx.arc(imageX + imageSize / 2, y + rowHeight / 2, imageSize / 2, 0, Math.PI * 2);
                    ctx.stroke();
                }
            }

            ctx.fillText(truncateCanvasText(ctx, cell, colWidths[index] - textOffset - 6), currentX + textOffset, y + rowHeight / 2 + 6);
            currentX += colWidths[index];
        });

        y += rowHeight;
    });

    return y + 32;
}

async function createDailyReportCanvas() {
    const report = getDailyReportData();
    const rankingRows = report.rankings;
    const matchRows = report.matches;
    const rankingSizes = rankingRows.map((_, index) => [96, 78, 68][index] || 34);
    const rankingImages = await Promise.all(report.rankingImages.map(async src => [
        null,
        await loadReportImage(src),
        null, null, null, null
    ]));
    const matchImages = await Promise.all(report.matchImages.map(async ([winner, loser]) => [
        null,
        await loadReportImage(winner),
        await loadReportImage(loser),
        null
    ]));
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");
    const rankingHeight = (rankingSizes.length ? rankingSizes.reduce((total, size) => total + size, 0) : 34) + 92;
    const matchesHeight = Math.max(1, matchRows.length) * (matchImages.length ? 40 : 34) + 92;

    canvas.width = 1100;
    canvas.height = 150 + rankingHeight + matchesHeight;

    ctx.fillStyle = "#f4f7fb";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = "#111827";
    ctx.fillRect(0, 0, canvas.width, 112);
    ctx.fillStyle = "#ffffff";
    ctx.textAlign = "center";
    ctx.font = "800 42px Arial";
    ctx.fillText("Reporte del Dia", canvas.width / 2, 48);
    ctx.font = "700 24px Arial";
    ctx.fillText(report.date || "Sin fecha", canvas.width / 2, 84);

    let y = 158;
    y = drawDailyReportTable(ctx, "Mini ranking del dia", ["#", "Jugador", "V", "D", "Puntos", "Manos"], rankingRows, y, [70, 420, 90, 90, 140, 250], rankingImages, rankingSizes);
    drawDailyReportTable(ctx, "Pleitos del dia", ["Fecha", "Ganador", "Perdedor", "Resultado"], matchRows, y, [280, 300, 300, 180], matchImages);

    return canvas;
}

async function exportDailyReportImage() {
    try {
        const blob = preparedDailyReportBlob || await dailyReportBlob();
        downloadBlob(blob, `reporte-dia-${getDailyReportData().date || "sin-fecha"}.jpg`);
    } catch (error) {
        showDailyReportError();
    }
}

async function shareDailyReport() {
    const isPrepared = Boolean(preparedDailyReportBlob);

    if (!isPrepared) {
        Swal.fire({
            title: "Preparando reporte",
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });
    }

    try {
        const blob = preparedDailyReportBlob || await dailyReportBlob();
        if (!isPrepared) {
            Swal.close();
        }
        const report = getDailyReportData();
        const filename = `reporte-dia-${report.date || "sin-fecha"}.jpg`;
        const file = new File([blob], filename, { type: "image/jpeg" });

        let canShareFile = false;

        try {
            canShareFile = Boolean(navigator.share && navigator.canShare?.({ files: [file] }));
        } catch (error) {
            canShareFile = false;
        }

        if (isPrepared && canShareFile) {
            try {
                await navigator.share({
                    title: "Reporte del Dia",
                    text: `Reporte del Dia ${report.date || ""}`.trim(),
                    files: [file]
                });
                return;
            } catch (error) {
                if (error.name === "AbortError") {
                    return;
                }
            }
        }

        downloadBlob(blob, filename);
        Swal.fire({
            icon: "info",
            title: "Reporte descargado",
            text: "Tu navegador no pudo abrir el menú para compartir. Puedes enviar el JPG descargado."
        });
    } catch (error) {
        Swal.close();
        showDailyReportError();
    }
}

function printDailyReport() {
    const report = getDailyReportData();
    const printWindow = window.open("", "_blank");

    if (!printWindow) {
        return;
    }

    const rankingRows = report.rankings.length > 0 ?
        report.rankings.map((row, rowIndex) => `<tr class="ranking-place-${rowIndex + 1}">${row.map((cell, cellIndex) => {
            const image = cellIndex === 1 ? report.rankingImages[rowIndex] : null;
            const thumbnail = image ? `<img src="${escapeHtml(image)}" alt="" class="ranking-character">` : "";

            return `<td>${thumbnail}${escapeHtml(cell)}</td>`;
        }).join("")}</tr>`).join("") :
        `<tr><td colspan="6">Sin datos para este dia</td></tr>`;
    const matchRows = report.matches.length > 0 ?
        report.matches.map((row, rowIndex) => `<tr>${row.map((cell, cellIndex) => {
            const image = report.matchImages[rowIndex]?.[cellIndex - 1];
            const thumbnail = image ? `<img src="${escapeHtml(image)}" alt="" class="character-thumbnail">` : "";

            return `<td>${thumbnail}${escapeHtml(cell)}</td>`;
        }).join("")}</tr>`).join("") :
        `<tr><td colspan="4">Sin pleitos para este dia</td></tr>`;

    printWindow.document.write(`
        <html>
            <head>
                <title>Reporte del Dia</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 32px; }
                    .header { text-align: center; margin-bottom: 24px; }
                    h1 { margin: 0 0 8px; }
                    h2 { margin: 24px 0 10px; }
                    .date { color: #4b5563; font-size: 20px; font-weight: 700; }
                    table { border-collapse: collapse; width: 100%; }
                    th { background: #0d6efd; color: #fff; text-align: left; }
                    th, td { border: 1px solid #dfe7f1; padding: 8px 10px; }
                    tr:nth-child(even) td { background: #f4f7fb; }
                    .ranking-place-1 td { background: #fff4ce !important; font-weight: 800; }
                    .ranking-place-2 td { background: #edf2f8 !important; font-weight: 700; }
                    .ranking-place-3 td { background: #f8eee6 !important; font-weight: 700; }
                    .ranking-character { border-radius: 50%; height: 56px; margin-right: 8px; object-fit: cover; vertical-align: middle; width: 56px; }
                    .ranking-place-1 .ranking-character { height: 84px; width: 84px; }
                    .ranking-place-2 .ranking-character { height: 68px; width: 68px; }
                    .character-thumbnail { border-radius: 50%; height: 28px; margin-right: 6px; object-fit: cover; vertical-align: middle; width: 28px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Reporte del Dia</h1>
                    <div class="date">${escapeHtml(report.date || "Sin fecha")}</div>
                </div>
                <h2>Mini ranking del dia</h2>
                <table>
                    <thead><tr><th>#</th><th>Jugador</th><th>V</th><th>D</th><th>Puntos</th><th>Manos</th></tr></thead>
                    <tbody>${rankingRows}</tbody>
                </table>
                <h2>Pleitos del dia</h2>
                <table>
                    <thead><tr><th>Fecha</th><th>Ganador</th><th>Perdedor</th><th>Resultado</th></tr></thead>
                    <tbody>${matchRows}</tbody>
                </table>
            </body>
        </html>
    `);
    printWindow.document.close();
    Promise.all(Array.from(printWindow.document.images).map(image => image.complete ? Promise.resolve() : new Promise(resolve => {
        image.onload = resolve;
        image.onerror = resolve;
    }))).then(() => {
        printWindow.focus();
        printWindow.print();
    });
}

function getQuickMatchDateText() {
    const value = document.getElementById("fechaPleito").value;

    if (!value) {
        return "Sin fecha";
    }

    return value.replace("T", " ");
}

function printQuickResult() {
    const result = getQuickResult();

    if (!result) {
        return;
    }

    const printWindow = window.open("", "_blank");
    const target = getQuickTarget();
    const matchDate = getQuickMatchDateText();

    if (!printWindow) {
        setQuickMessage("El navegador bloqueo la ventana de impresion.", "warning");
        return;
    }

    printWindow.document.write(`
        <html>
            <head>
                <title>Resultado del Match</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; }
                    .card { background: #f4f7fb; border: 1px solid #dfe7f1; border-radius: 8px; padding: 32px; text-align: center; }
                    .header { margin-bottom: 24px; text-align: center; }
                    h1 { margin: 0; }
                    .date { color: #4b5563; font-size: 20px; font-weight: 700; margin-top: 8px; }
                    .result-row { align-items: center; display: grid; gap: 18px; grid-template-columns: 1fr 72px 1fr; margin: 32px auto; max-width: 620px; }
                    .player { border: 1px solid #dfe7f1; border-radius: 8px; padding: 24px; position: relative; }
                    .label { color: #4b5563; font-weight: 700; margin-bottom: 12px; }
                    .name-row { align-items: center; display: flex; justify-content: center; }
                    .name { font-size: 28px; font-weight: 800; }
                    .score { color: #0d6efd; font-size: 72px; font-weight: 800; line-height: 1; margin-top: 18px; }
                    .ranking-block { color: #4b5563; position: absolute; text-align: center; top: 72px; width: 62px; }
                    .winner-ranking { left: 18px; }
                    .loser-ranking { right: 18px; }
                    .ranking-label { font-size: 12px; font-weight: 700; }
                    .ranking-number { font-size: 18px; font-weight: 800; margin-top: 2px; }
                    .versus { color: #4b5563; font-size: 30px; font-weight: 800; text-transform: lowercase; }
                    .meta { color: #4b5563; font-size: 22px; }
                </style>
            </head>
            <body>
                <div class="card">
                    <div class="header">
                        <h1>Resultado del Match</h1>
                        <div class="date">${escapeHtml(matchDate)}</div>
                    </div>
                    <div class="meta">Primero a ganar ${target} manos</div>
                    <div class="result-row">
                        <div class="player">
                            <div class="label">Ganador</div>
                            <div class="ranking-block winner-ranking">
                                <div class="ranking-label">Ranking</div>
                                <div class="ranking-number">#${escapeHtml(result.winnerRanking)}</div>
                            </div>
                            <div class="name-row">
                                <div class="name">${escapeHtml(result.winnerName)}</div>
                            </div>
                            <div class="score">${result.winnerScore}</div>
                        </div>
                        <div class="versus">vs</div>
                        <div class="player">
                            <div class="label">Perdedor</div>
                            <div class="ranking-block loser-ranking">
                                <div class="ranking-label">Ranking</div>
                                <div class="ranking-number">#${escapeHtml(result.loserRanking)}</div>
                            </div>
                            <div class="name-row">
                                <div class="name">${escapeHtml(result.loserName)}</div>
                            </div>
                            <div class="score">${result.loserScore}</div>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}

function updateQuickMatch() {
    const target = getQuickTarget();
    const winner = getQuickWinner();
    const player1Name = getSelectedPlayerName(quickPlayer1, "Player 1");
    const player2Name = getSelectedPlayerName(quickPlayer2, "Player 2");

    quickScore1.textContent = quickMatchScores[1];
    quickScore2.textContent = quickMatchScores[2];
    quickPlayerName1.textContent = player1Name;
    quickPlayerName2.textContent = player2Name;
    quickPlayerPanel1.classList.toggle("is-winning", winner === 1);
    quickPlayerPanel2.classList.toggle("is-winning", winner === 2);

    clearQuickHiddenFields();
    quickSaveButton.disabled = true;
    quickShareActions.classList.remove("is-visible");

    document.querySelectorAll("[data-quick-action='plus']").forEach(button => {
        button.disabled = !quickMatchStarted || winner !== 0;
    });

    document.querySelectorAll("[data-quick-action='minus']").forEach(button => {
        const player = button.dataset.quickPlayer;
        button.disabled = !quickMatchStarted || quickMatchScores[player] === 0;
    });

    if (!quickPlayer1.value || !quickPlayer2.value) {
        setQuickMessage(`Este match es primero a ganar ${target} manos.`, "info");
        return;
    }

    if (quickPlayer1.value === quickPlayer2.value) {
        setQuickMessage("Player 1 y Player 2 no pueden ser el mismo jugador.", "danger");
        return;
    }

    if (!winner) {
        setQuickMessage(`Este match es primero a ganar ${target} manos.`, "warning");
        return;
    }

    if (winner === 1) {
        quickGanadorId.value = quickPlayer1.value;
        quickPerdedorId.value = quickPlayer2.value;
        quickGanadorImageId.value = getQuickImage(1);
        quickPerdedorImageId.value = getQuickImage(2);
        quickVictoriasGanador.value = quickMatchScores[1];
        quickVictoriasPerdedor.value = quickMatchScores[2];
        setQuickMessage(`${player1Name} gana ${quickMatchScores[1]} - ${quickMatchScores[2]}.`, "success");
    } else {
        quickGanadorId.value = quickPlayer2.value;
        quickPerdedorId.value = quickPlayer1.value;
        quickGanadorImageId.value = getQuickImage(2);
        quickPerdedorImageId.value = getQuickImage(1);
        quickVictoriasGanador.value = quickMatchScores[2];
        quickVictoriasPerdedor.value = quickMatchScores[1];
        setQuickMessage(`${player2Name} gana ${quickMatchScores[2]} - ${quickMatchScores[1]}.`, "success");
    }

    quickSaveButton.disabled = false;
    quickShareActions.classList.add("is-visible");
}

document.querySelectorAll("[data-quick-player]").forEach(button => {
    button.addEventListener("click", () => {
        const target = getQuickTarget();
        const player = button.dataset.quickPlayer;
        const action = button.dataset.quickAction;

        if (action === "plus" && !getQuickWinner() && quickMatchScores[player] < target) {
            quickMatchScores[player]++;
        }

        if (action === "minus" && quickMatchScores[player] > 0) {
            quickMatchScores[player]--;
        }

        updateQuickMatch();
    });
});

document.querySelectorAll("input[name='formato_pleito']").forEach(option => {
    option.addEventListener("change", () => {
        resetQuickScores();
        validateQuickSetup();
        updateQuickMatch();
    });
});

quickPlayer1.addEventListener("change", () => {
    validateQuickSetup();
    updateQuickMatch();
});

quickPlayer2.addEventListener("change", () => {
    validateQuickSetup();
    updateQuickMatch();
});

document.querySelectorAll("[data-quick-image-picker]").forEach(picker => {
    picker.addEventListener("change", updateQuickMatch);
});

quickStartButton.addEventListener("click", () => {
    if (!validateQuickSetup()) {
        return;
    }

    confirmEarlyMorning(document.getElementById("fechaPleito").value, showQuickCounter);
});

quickEditButton.addEventListener("click", showQuickSetup);

quickShareImageButton.addEventListener("click", () => {
    const result = getQuickResult();

    if (!result) {
        return;
    }

    createQuickResultCanvas().toBlob(blob => {
        const filename = "resultado-pleito.png";
        const file = new File([blob], filename, {
            type: "image/png"
        });

        if (navigator.canShare && navigator.canShare({
                files: [file]
            })) {
            navigator.share({
                title: "Resultado del pleito",
                text: result.line,
                files: [file]
            });
            return;
        }

        downloadBlob(blob, filename);
    }, "image/png");
});

quickPrintButton.addEventListener("click", printQuickResult);
dailyReportShareButton.addEventListener("click", shareDailyReport);
dailyReportImageButton.addEventListener("click", exportDailyReportImage);
dailyReportPrintButton.addEventListener("click", printDailyReport);

function showSavingResults(form) {
    Swal.fire({
        title: "Guardando resultados",
        text: "Espera un momento...",
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    });

    setTimeout(() => form.submit(), 100);
}

quickMatchForm.addEventListener("submit", event => {
    updateQuickMatch();

    if (!quickMatchStarted || quickSaveButton.disabled) {
        event.preventDefault();

        return;
    }

    event.preventDefault();
    quickSaveButton.disabled = true;
    showSavingResults(quickMatchForm);
});

quickModal.addEventListener("hidden.bs.modal", () => {
    quickMatchForm.reset();
    showQuickSetup();
    setQuickSetupMessage("Selecciona el formato y los dos jugadores.", "info");
});

showQuickSetup();

const editMatchForm = document.getElementById("editMatchForm");
const editFechaMatch = document.getElementById("editFechaMatch");
const editGanadorMatch = document.getElementById("editGanadorMatch");
const editPerdedorMatch = document.getElementById("editPerdedorMatch");
const editVictoriasGanador = document.getElementById("editVictoriasGanador");
const editVictoriasPerdedor = document.getElementById("editVictoriasPerdedor");
const editMatchPlayers = document.getElementById("editMatchPlayers");
const editMatchDate = document.getElementById("editMatchDate");

function normalizeDateTimeLocal(value) {
    return value ? value.replace(" ", "T").slice(0, 16) : "";
}

function updateEditLoserMax() {
    const max = parseInt(editVictoriasGanador.value, 10) - 1;
    editVictoriasPerdedor.max = max;

    if (parseInt(editVictoriasPerdedor.value, 10) > max) {
        editVictoriasPerdedor.value = max;
    }
}

document.getElementById("matchesTable").addEventListener("click", event => {
    const button = event.target.closest(".edit-match-button");

    if (button) {
        editMatchForm.action = button.dataset.updateUrl;
        editFechaMatch.value = normalizeDateTimeLocal(button.dataset.fecha);
        editFechaMatch.dataset.original = editFechaMatch.value;
        editGanadorMatch.value = button.dataset.ganadorId;
        editPerdedorMatch.value = button.dataset.perdedorId;
        editVictoriasGanador.value = button.dataset.victoriasGanador;
        editVictoriasPerdedor.value = button.dataset.victoriasPerdedor;
        editMatchPlayers.textContent = `${button.dataset.ganador} vs ${button.dataset.perdedor}`;
        editMatchDate.textContent = button.dataset.fecha;
        updateEditLoserMax();
    }
});

editVictoriasGanador.addEventListener("change", updateEditLoserMax);

editMatchForm.addEventListener("submit", event => {
    event.preventDefault();

    if (editFechaMatch.value !== editFechaMatch.dataset.original && isEarlyMorningDate(editFechaMatch.value)) {
        confirmEarlyMorning(editFechaMatch.value, () => {
            showSavingResults(editMatchForm);
        });

        return;
    }

    showSavingResults(editMatchForm);
});


if (shouldOpenDailyReport) {
    const dailyReportModal = new bootstrap.Modal(document.getElementById("modalReporteDia"));
    dailyReportModal.show();
    clearDailyReportQuery();
}

const modalEstadisticasElement = document.getElementById("modalEstadisticas");
const modalEstadisticas = new bootstrap.Modal(modalEstadisticasElement);
const cboPlayerEstadistica = document.getElementById("cboPlayerEstadistica");

document.querySelectorAll(".btn-ver-estadisticas").forEach(button => {
    button.addEventListener("click", () => {
        const playerId = button.dataset.playerId;
        cboPlayerEstadistica.value = playerId;
        cboPlayerEstadistica.dispatchEvent(new Event("change"));
        modalEstadisticas.show();
    });
});

cboPlayerEstadistica.addEventListener("change", () => {
    let player = cboPlayerEstadistica.value;
    fetch(`${sf2Routes.playerStats}?player=${encodeURIComponent(player)}&season=${encodeURIComponent(selectedSeason)}`)
        .then(data => data.json())
        .then(({
            data,
            totales,
            posicion
        }) => {
            const tableEstadisticas = document.querySelector("#tableEstadisticas tbody");
            tableEstadisticas.innerHTML = `<tr><td colspan=8>No Hay Matches</td></tr>`;

            if (data.length > 0) {
                tableEstadisticas.innerHTML = "";

                data.forEach(match => {
                    let porcentajeVictorias = parseInt(match.porcentaje_victorias);
                    let porcentajeDerrotas = parseInt(match.porcentaje_derrotas);
                    const tendenciaIcono = match.tendencia_porcentaje === "up"
                        ? '<span class="text-success fw-bold ms-1" title="Subiendo">&#9650;</span>'
                        : '<span class="text-danger fw-bold ms-1" title="Bajando">&#9660;</span>';

                    let colorRow = porcentajeVictorias > porcentajeDerrotas ? "bg-green" : "bg-red";
                    tableEstadisticas.innerHTML += `<tr class="${colorRow}">
                        <td>${match.player}</td>
                        <td>VS</td>
                        <td>${match.vs_player}</td>
                        <td>${parseInt(match.promedio_victorias) + parseInt(match.promedio_derrotas)}</td>
                        <td>${match.promedio_victorias}</td>
                        <td>${match.promedio_derrotas}</td>
                        <td>${porcentajeVictorias} % ${tendenciaIcono}</td>
                        <td>${porcentajeDerrotas} %</td>
                    </tr>`;
                });
            }

            const {
                totalMatch,
                totalVictorias,
                totalDerrotas,
                porcentajeVictoria,
                porcentajeDerrota
            } = totales

            document.querySelector("#totalMatch").innerHTML = totalMatch;
            document.querySelector("#totalVictorias").innerHTML = totalVictorias;
            document.querySelector("#totalDerrotas").innerHTML = totalDerrotas;
            document.querySelector("#porcentajeVictoria").innerHTML = porcentajeVictoria;
            document.querySelector("#porcentajeDerrota").innerHTML = porcentajeDerrota;

            document.querySelector("#porcentajeVictoria").closest("h2").classList.remove("text-danger", "text-success");
            document.querySelector("#totalVictorias").classList.remove("text-danger", "text-success");

            if (porcentajeVictoria > porcentajeDerrota) {
                document.querySelector("#porcentajeVictoria").closest("h2").classList.add("text-success");
                document.querySelector("#totalVictorias").classList.add("text-success");
            } else {
                document.querySelector("#porcentajeVictoria").closest("h2").classList.add("text-danger");
                document.querySelector("#totalVictorias").classList.add("text-danger");
            }

            document.querySelector("#porcentajeDerrota").closest("h2").classList.add("text-danger");
            document.querySelector("#totalDerrotas").classList.add("text-danger");

            document.querySelector("#ranking").innerHTML = posicion;
        });
})

// VS del Dia
document.getElementById('vsBuscarBtn').addEventListener('click', () => {
    const fecha    = document.getElementById('vsFecha').value;
    const player1  = document.getElementById('vsPlayer1Sel').value;
    const player2  = document.getElementById('vsPlayer2Sel').value;
    const errorDiv = document.getElementById('vsError');
    const resultDiv = document.getElementById('vsResultados');

    errorDiv.classList.add('d-none');
    resultDiv.classList.add('d-none');

    if (!fecha) {
        errorDiv.textContent = 'Selecciona una fecha.';
        errorDiv.classList.remove('d-none');
        return;
    }
    if (!player1 || !player2) {
        errorDiv.textContent = 'Selecciona ambos players.';
        errorDiv.classList.remove('d-none');
        return;
    }
    if (player1 === player2) {
        errorDiv.textContent = 'Selecciona dos players diferentes.';
        errorDiv.classList.remove('d-none');
        return;
    }

    fetch(`${sf2Routes.versus}?player1=${encodeURIComponent(player1)}&player2=${encodeURIComponent(player2)}&fecha=${encodeURIComponent(fecha)}`, {
    headers: { Accept: "application/json" }
})
        .then(r => r.json())
        .then(({ stats1, stats2, h2h, matches1, matches2, error }) => {
            if (error) {
                errorDiv.textContent = error;
                errorDiv.classList.remove('d-none');
                return;
            }

            document.getElementById('vsTitulo').textContent = `${stats1.nombre} vs ${stats2.nombre} — ${fecha}`;

            function calcH2H(h2h, playerName) {
                let v = 0, d = 0, mf = 0, mc = 0;
                h2h.forEach(m => {
                    if (m.ganador === playerName) {
                        v++; mf += parseInt(m.victorias_ganador); mc += parseInt(m.victorias_perdedor);
                    } else if (m.perdedor === playerName) {
                        d++; mf += parseInt(m.victorias_perdedor); mc += parseInt(m.victorias_ganador);
                    }
                });
                return { nombre: playerName, victorias: v, derrotas: d, puntos: mf - mc, manos_favor: mf, manos_contra: mc };
            }

            function fillCard(suffix, stats) {
                document.getElementById('vsNombre' + suffix).textContent = stats.nombre;
                document.getElementById('vsV' + suffix).textContent = stats.victorias;
                document.getElementById('vsD' + suffix).textContent = stats.derrotas;
                const card = document.getElementById('vsCard' + suffix);
                card.className = 'card text-center shadow border-2';
                if (parseInt(stats.victorias) > parseInt(stats.derrotas)) {
                    card.classList.add('border-success');
                } else if (parseInt(stats.victorias) < parseInt(stats.derrotas)) {
                    card.classList.add('border-danger');
                }
            }

            fillCard('1', calcH2H(h2h, stats1.nombre));
            fillCard('2', calcH2H(h2h, stats2.nombre));

            const h2hBody = document.querySelector('#vsH2HTable tbody');
            if (h2h.length === 0) {
                h2hBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hubo pleitos directos ese dia.</td></tr>';
            } else {
                h2hBody.innerHTML = '';
                h2h.forEach(m => {
                    const hora = m.fecha.includes(' ') ? m.fecha.split(' ')[1] : m.fecha;
                    h2hBody.innerHTML += `<tr>
                        <td>${hora}</td>
                        <td class="text-success fw-bold">${m.ganador}</td>
                        <td class="text-danger">${m.perdedor}</td>
                        <td>${m.victorias_ganador}-${m.victorias_perdedor}</td>
                    </tr>`;
                });
            }

            function renderMatchesTable(tableId, titleId, matches, playerName) {
                document.getElementById(titleId).textContent = `Pleitos de ${playerName} ese dia`;
                const tbody = document.querySelector(`#${tableId} tbody`);
                if (matches.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay pleitos registrados.</td></tr>';
                    return;
                }
                tbody.innerHTML = '';
                matches.forEach(m => {
                    const hora = m.fecha.includes(' ') ? m.fecha.split(' ')[1] : m.fecha;
                    const esV = m.vd === 'V';
                    const badge = esV
                        ? '<span class="badge bg-success">V</span>'
                        : '<span class="badge bg-danger">D</span>';
                    tbody.innerHTML += `<tr class="${esV ? 'table-success' : 'table-danger'}">
                        <td>${hora}</td>
                        <td>${m.rival}</td>
                        <td>${badge}</td>
                        <td>${m.manos_favor}-${m.manos_contra}</td>
                    </tr>`;
                });
            }

            renderMatchesTable('vsMatches1Table', 'vsMatchesTitle1', matches1, stats1.nombre);
            renderMatchesTable('vsMatches2Table', 'vsMatchesTitle2', matches2, stats2.nombre);

            resultDiv.classList.remove('d-none');
        })
        .catch(err => {
            errorDiv.textContent = 'Error: ' + err.message;
            errorDiv.classList.remove('d-none');
        });
});

function getVSReportData() {
    return {
        fecha: document.getElementById('vsFecha').value,
        titulo: document.getElementById('vsTitulo').textContent,
        player1: {
            nombre: document.getElementById('vsNombre1').textContent,
            v: document.getElementById('vsV1').textContent,
            d: document.getElementById('vsD1').textContent
        },
        player2: {
            nombre: document.getElementById('vsNombre2').textContent,
            v: document.getElementById('vsV2').textContent,
            d: document.getElementById('vsD2').textContent
        },
        h2h: getTableRows('vsH2HTable', 4),
        matches1: getTableRows('vsMatches1Table', 4),
        matches2: getTableRows('vsMatches2Table', 4)
    };
}

function roundRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.lineTo(x + w - r, y);
    ctx.quadraticCurveTo(x + w, y, x + w, y + r);
    ctx.lineTo(x + w, y + h - r);
    ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
    ctx.lineTo(x + r, y + h);
    ctx.quadraticCurveTo(x, y + h, x, y + h - r);
    ctx.lineTo(x, y + r);
    ctx.quadraticCurveTo(x, y, x + r, y);
    ctx.closePath();
}

function drawVSCard(ctx, x, y, width, name, v, d) {
    const height = 160;
    let borderColor = '#dee2e6';
    if (parseInt(v) > parseInt(d)) borderColor = '#198754';
    else if (parseInt(v) < parseInt(d)) borderColor = '#dc3545';

    ctx.fillStyle = '#ffffff';
    roundRect(ctx, x, y, width, height, 10);
    ctx.fill();

    ctx.fillStyle = '#f8f9fa';
    roundRect(ctx, x, y, width, 50, 10);
    ctx.fill();
    ctx.fillRect(x, y + 40, width, 10);

    ctx.strokeStyle = '#dee2e6';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(x, y + 50);
    ctx.lineTo(x + width, y + 50);
    ctx.stroke();

    ctx.strokeStyle = borderColor;
    ctx.lineWidth = 4;
    roundRect(ctx, x, y, width, height, 10);
    ctx.stroke();

    ctx.fillStyle = '#212529';
    ctx.font = '700 22px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(truncateCanvasText(ctx, name, width - 24), x + width / 2, y + 33);

    ctx.font = '500 16px Arial';
    ctx.fillStyle = '#6c757d';
    ctx.fillText('V', x + width / 4, y + 85);
    ctx.fillText('D', x + (width * 3) / 4, y + 85);

    ctx.font = '800 44px Arial';
    ctx.fillStyle = '#198754';
    ctx.fillText(String(v || 0), x + width / 4, y + 135);
    ctx.fillStyle = '#dc3545';
    ctx.fillText(String(d || 0), x + (width * 3) / 4, y + 135);
}

function drawVSTable(ctx, x, y, title, headers, colWidths, rows, rowStyler) {
    const rowHeight = 38;
    const tableWidth = colWidths.reduce((a, b) => a + b, 0);

    ctx.fillStyle = '#111827';
    ctx.font = '700 22px Arial';
    ctx.textAlign = 'left';
    ctx.fillText(title, x, y);
    y += 16;

    ctx.fillStyle = '#212529';
    ctx.fillRect(x, y, tableWidth, rowHeight);
    ctx.fillStyle = '#ffffff';
    ctx.font = '700 16px Arial';
    let cx = x;
    headers.forEach((h, i) => {
        ctx.fillText(h, cx + 14, y + 25);
        cx += colWidths[i];
    });
    y += rowHeight;

    if (rows.length === 0) {
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(x, y, tableWidth, rowHeight);
        ctx.strokeStyle = '#dee2e6';
        ctx.lineWidth = 1;
        ctx.strokeRect(x, y, tableWidth, rowHeight);
        ctx.fillStyle = '#6c757d';
        ctx.font = '600 16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Sin datos', x + tableWidth / 2, y + 25);
        ctx.textAlign = 'left';
        return y + rowHeight + 32;
    }

    rows.forEach((row, ri) => {
        const style = rowStyler ? rowStyler(row, ri) : { bg: ri % 2 === 0 ? '#ffffff' : '#f8f9fa' };
        ctx.fillStyle = style.bg;
        ctx.fillRect(x, y, tableWidth, rowHeight);
        ctx.strokeStyle = '#dee2e6';
        ctx.lineWidth = 1;
        ctx.strokeRect(x, y, tableWidth, rowHeight);

        cx = x;
        row.forEach((cell, ci) => {
            const cellStyle = (style.cells && style.cells[ci]) || null;
            if (cellStyle && cellStyle.badge) {
                const text = String(cell);
                ctx.font = '700 14px Arial';
                const tw = ctx.measureText(text).width;
                const bw = Math.max(tw + 24, 38);
                const bh = 26;
                const bx = cx + (colWidths[ci] - bw) / 2;
                const by = y + (rowHeight - bh) / 2;
                roundRect(ctx, bx, by, bw, bh, 13);
                ctx.fillStyle = cellStyle.badgeColor;
                ctx.fill();
                ctx.fillStyle = '#ffffff';
                ctx.textAlign = 'center';
                ctx.fillText(text, bx + bw / 2, by + 18);
                ctx.textAlign = 'left';
            } else {
                ctx.fillStyle = (cellStyle && cellStyle.color) || '#212529';
                ctx.font = (cellStyle && cellStyle.bold) ? '700 16px Arial' : '500 16px Arial';
                ctx.fillText(truncateCanvasText(ctx, cell, colWidths[ci] - 20), cx + 14, y + 25);
            }
            cx += colWidths[ci];
        });
        y += rowHeight;
    });

    return y + 32;
}

function createVSCanvas() {
    const data = getVSReportData();
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    const tableContrib = n => 92 + 38 * Math.max(1, n);

    canvas.width = 1100;
    canvas.height = 360
        + tableContrib(data.h2h.length)
        + tableContrib(data.matches1.length)
        + tableContrib(data.matches2.length)
        + 20;

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = '#dc3545';
    ctx.fillRect(0, 0, canvas.width, 110);
    ctx.fillStyle = '#ffffff';
    ctx.textAlign = 'center';
    ctx.font = '800 44px Arial';
    ctx.fillText('VS del Dia', canvas.width / 2, 50);
    ctx.font = '700 22px Arial';
    ctx.fillText(data.titulo || data.fecha || 'Sin fecha', canvas.width / 2, 88);

    const cardY = 140;
    const cardWidth = 440;
    const x1 = 60;
    const x2 = canvas.width - 60 - cardWidth;
    drawVSCard(ctx, x1, cardY, cardWidth, data.player1.nombre, data.player1.v, data.player1.d);
    drawVSCard(ctx, x2, cardY, cardWidth, data.player2.nombre, data.player2.v, data.player2.d);

    ctx.fillStyle = '#6c757d';
    ctx.font = '900 64px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('VS', canvas.width / 2, cardY + 105);

    const h2hStyler = (row, ri) => ({
        bg: ri % 2 === 0 ? '#ffffff' : '#f8f9fa',
        cells: [
            null,
            { color: '#198754', bold: true },
            { color: '#dc3545' },
            null
        ]
    });

    const matchStyler = (row) => {
        const isV = row[2] === 'V';
        return {
            bg: isV ? '#d1e7dd' : '#f8d7da',
            cells: [
                null,
                null,
                { badge: true, badgeColor: isV ? '#198754' : '#dc3545' },
                null
            ]
        };
    };

    let y = 340;
    y = drawVSTable(ctx, 60, y, 'Pleitos directos ese dia', ['Hora', 'Ganador', 'Perdedor', 'Resultado'], [260, 320, 320, 180], data.h2h, h2hStyler);
    y = drawVSTable(ctx, 60, y, `Pleitos de ${data.player1.nombre} ese dia`, ['Hora', 'Rival', 'V/D', 'Resultado'], [260, 360, 120, 240], data.matches1, matchStyler);
    drawVSTable(ctx, 60, y, `Pleitos de ${data.player2.nombre} ese dia`, ['Hora', 'Rival', 'V/D', 'Resultado'], [260, 360, 120, 240], data.matches2, matchStyler);

    return canvas;
}

function shareVSReport() {
    if (document.getElementById('vsResultados').classList.contains('d-none')) {
        return;
    }
    createVSCanvas().toBlob(blob => {
        const data = getVSReportData();
        const filename = `vs-dia-${data.fecha || 'sin-fecha'}.jpg`;
        const file = new File([blob], filename, { type: 'image/jpeg' });

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({
                title: 'VS del Dia',
                text: data.titulo,
                files: [file]
            });
            return;
        }

        downloadBlob(blob, filename);
    }, 'image/jpeg', 0.92);
}

document.getElementById('vsShareButton').addEventListener('click', shareVSReport);
