# -*- coding: utf-8 -*-
"""Genera el manual de usuario del sistema Becomarca (DOCX)."""

from docx import Document
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor

NAVY = RGBColor(0x1F, 0x38, 0x64)
BLUE = RGBColor(0x2E, 0x74, 0xB5)
GREY = RGBColor(0x59, 0x59, 0x59)
LIGHT = "EAF1FB"
OK_GREEN = "E8F3E8"
WARN = "FFF4E5"

OUT = "docs/Manual_Becomarca.docx"


def set_cell_bg(cell, hex_color):
    tc_pr = cell._tc.get_or_add_tcPr()
    shd = OxmlElement("w:shd")
    shd.set(qn("w:val"), "clear")
    shd.set(qn("w:color"), "auto")
    shd.set(qn("w:fill"), hex_color)
    tc_pr.append(shd)


def shade_paragraph(paragraph, hex_color):
    p_pr = paragraph._p.get_or_add_pPr()
    shd = OxmlElement("w:shd")
    shd.set(qn("w:val"), "clear")
    shd.set(qn("w:color"), "auto")
    shd.set(qn("w:fill"), hex_color)
    p_pr.append(shd)


def bottom_border(paragraph, color="1F3864", size="8"):
    p_pr = paragraph._p.get_or_add_pPr()
    borders = OxmlElement("w:pBdr")
    bottom = OxmlElement("w:bottom")
    bottom.set(qn("w:val"), "single")
    bottom.set(qn("w:sz"), size)
    bottom.set(qn("w:space"), "4")
    bottom.set(qn("w:color"), color)
    borders.append(bottom)
    p_pr.append(borders)


def add_page_number(paragraph):
    run = paragraph.add_run()
    fld_begin = OxmlElement("w:fldChar")
    fld_begin.set(qn("w:fldCharType"), "begin")
    instr = OxmlElement("w:instrText")
    instr.set(qn("xml:space"), "preserve")
    instr.text = "PAGE"
    fld_end = OxmlElement("w:fldChar")
    fld_end.set(qn("w:fldCharType"), "end")
    run._r.append(fld_begin)
    run._r.append(instr)
    run._r.append(fld_end)


def setup(doc):
    section = doc.sections[0]
    section.page_height = Cm(29.7)
    section.page_width = Cm(21.0)
    section.top_margin = Cm(2.1)
    section.bottom_margin = Cm(2.0)
    section.left_margin = Cm(2.2)
    section.right_margin = Cm(2.2)
    section.different_first_page_header_footer = True

    normal = doc.styles["Normal"]
    normal.font.name = "Calibri"
    normal.font.size = Pt(10.5)
    normal.font.color.rgb = RGBColor(0x22, 0x22, 0x22)
    normal.paragraph_format.space_after = Pt(6)
    normal.paragraph_format.line_spacing = 1.12

    for name, size, color, before, after in [
        ("Heading 1", 17, NAVY, 10, 8),
        ("Heading 2", 12.5, BLUE, 10, 4),
        ("Heading 3", 11, GREY, 8, 3),
    ]:
        style = doc.styles[name]
        style.font.name = "Calibri"
        style.font.size = Pt(size)
        style.font.bold = True
        style.font.color.rgb = color
        style.paragraph_format.space_before = Pt(before)
        style.paragraph_format.space_after = Pt(after)
        style.paragraph_format.keep_with_next = True

    # Pie de pagina
    footer = section.footer
    fp = footer.paragraphs[0]
    fp.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = fp.add_run("Laboratorio Becomarca · Manual de uso · ")
    run.font.size = Pt(8)
    run.font.color.rgb = GREY
    add_page_number(fp)
    for r in fp.runs:
        r.font.size = Pt(8)
        r.font.color.rgb = GREY


def h1(doc, text, page_break=False):
    if page_break:
        doc.add_page_break()
    p = doc.add_paragraph(text, style="Heading 1")
    bottom_border(p, "2E74B5", "6")
    return p


def h2(doc, text):
    return doc.add_paragraph(text, style="Heading 2")


def para(doc, text, size=10.5, italic=False, color=None, space_after=6):
    p = doc.add_paragraph()
    run = p.add_run(text)
    run.font.size = Pt(size)
    run.italic = italic
    if color:
        run.font.color.rgb = color
    p.paragraph_format.space_after = Pt(space_after)
    return p


def lead(doc, label, text):
    p = doc.add_paragraph()
    r1 = p.add_run(label + " ")
    r1.bold = True
    r1.font.size = Pt(10.5)
    r2 = p.add_run(text)
    r2.font.size = Pt(10.5)
    return p


def bullets(doc, items, size=10.5):
    for item in items:
        p = doc.add_paragraph(style="List Bullet")
        p.paragraph_format.space_after = Pt(3)
        if isinstance(item, tuple):
            r1 = p.add_run(item[0] + " ")
            r1.bold = True
            r1.font.size = Pt(size)
            r2 = p.add_run(item[1])
            r2.font.size = Pt(size)
        else:
            r = p.add_run(item)
            r.font.size = Pt(size)


def steps(doc, items):
    for i, item in enumerate(items, 1):
        p = doc.add_paragraph()
        p.paragraph_format.left_indent = Cm(0.6)
        p.paragraph_format.first_line_indent = Cm(-0.6)
        p.paragraph_format.space_after = Pt(3)
        r1 = p.add_run(f"{i}. ")
        r1.bold = True
        r1.font.size = Pt(10.5)
        if isinstance(item, tuple):
            r2 = p.add_run(item[0] + " ")
            r2.bold = True
            r2.font.size = Pt(10.5)
            r3 = p.add_run(item[1])
            r3.font.size = Pt(10.5)
        else:
            r2 = p.add_run(item)
            r2.font.size = Pt(10.5)


def callout(doc, title, text, kind="info"):
    fill = {"info": LIGHT, "warn": WARN, "ok": OK_GREEN}[kind]
    border = {"info": "2E74B5", "warn": "C55A11", "ok": "548235"}[kind]
    table = doc.add_table(rows=1, cols=1)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = True
    cell = table.cell(0, 0)
    set_cell_bg(cell, fill)
    cell.width = Cm(16.2)
    p = cell.paragraphs[0]
    p.paragraph_format.space_before = Pt(4)
    p.paragraph_format.space_after = Pt(4)
    r1 = p.add_run(title + "  ")
    r1.bold = True
    r1.font.size = Pt(9.5)
    r1.font.color.rgb = RGBColor.from_string(border)
    r2 = p.add_run(text)
    r2.font.size = Pt(9.5)
    r2.font.color.rgb = NAVY
    doc.add_paragraph().paragraph_format.space_after = Pt(2)


def set_table_widths(table, widths):
    """Fija el ancho de columnas (python-docx solo no lo respeta)."""
    table.autofit = False
    tbl_pr = table._tbl.tblPr
    layout = OxmlElement("w:tblLayout")
    layout.set(qn("w:type"), "fixed")
    tbl_pr.append(layout)
    for i, width in enumerate(widths):
        table.columns[i].width = Cm(width)
        for cell in table.columns[i].cells:
            cell.width = Cm(width)


def table(doc, headers, rows, widths=None, header_fill="1F3864"):
    t = doc.add_table(rows=1, cols=len(headers))
    t.style = "Table Grid"
    t.alignment = WD_TABLE_ALIGNMENT.CENTER
    hdr = t.rows[0].cells
    for i, text in enumerate(headers):
        set_cell_bg(hdr[i], header_fill)
        p = hdr[i].paragraphs[0]
        p.paragraph_format.space_after = Pt(2)
        r = p.add_run(text)
        r.bold = True
        r.font.size = Pt(9.5)
        r.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
    for row in rows:
        cells = t.add_row().cells
        for i, text in enumerate(row):
            p = cells[i].paragraphs[0]
            p.paragraph_format.space_after = Pt(2)
            r = p.add_run(text)
            r.font.size = Pt(9.5)
    if widths:
        set_table_widths(t, widths)
    doc.add_paragraph().paragraph_format.space_after = Pt(2)
    return t


def cover(doc):
    for _ in range(2):
        doc.add_paragraph()

    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run("LABORATORIO DE ANATOMÍA PATOLÓGICA")
    r.font.size = Pt(11)
    r.font.color.rgb = GREY
    r.bold = True
    r.font.name = "Calibri"

    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run('"Dr. Juan Carlos Becker"')
    r.font.size = Pt(13)
    r.font.color.rgb = NAVY
    r.italic = True

    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run("BECOMARCA")
    r.font.size = Pt(10)
    r.font.color.rgb = GREY
    r.bold = True
    p.paragraph_format.space_after = Pt(30)

    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run("Manual de uso")
    r.font.size = Pt(34)
    r.bold = True
    r.font.color.rgb = NAVY

    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run("Sistema de gestión de pacientes, estudios e informes")
    r.font.size = Pt(13)
    r.font.color.rgb = BLUE
    p.paragraph_format.space_after = Pt(24)

    for _ in range(6):
        doc.add_paragraph()

    t = doc.add_table(rows=3, cols=2)
    t.alignment = WD_TABLE_ALIGNMENT.CENTER
    t.autofit = False
    data = [
        ("Para", "Médico patólogo y personal del laboratorio"),
        ("Versión", "1.0 — Noviembre 2026"),
        ("Reemplaza", "Sistema anterior en Microsoft Access"),
    ]
    for i, (k, v) in enumerate(data):
        c0, c1 = t.rows[i].cells
        r0 = c0.paragraphs[0].add_run(k)
        r0.bold = True
        r0.font.size = Pt(9.5)
        r0.font.color.rgb = GREY
        r1 = c1.paragraphs[0].add_run(v)
        r1.font.size = Pt(9.5)
    set_table_widths(t, [3.4, 11.6])

    for _ in range(10):
        doc.add_paragraph()

    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run("Documento de uso interno")
    r.font.size = Pt(8.5)
    r.font.color.rgb = GREY
    bottom_border(p, "BFBFBF", "6")


def build():
    doc = Document()
    setup(doc)
    cover(doc)

    # ------------------------------------------------------------------ indice
    doc.add_page_break()
    h1(doc, "Contenido")
    table(
        doc,
        ["Sección", "Qué encontrás"],
        [
            ("1. Qué es este sistema", "Para qué sirve, qué reemplaza y el flujo diario."),
            ("2. Cómo entrar", "Dirección, usuario, contraseña y qué hacer si no entra."),
            ("3. La pantalla principal", "El menú y las secciones según el rol."),
            ("4. Pacientes", "Buscar, crear, corregir y ver la ficha."),
            ("5. Estudios", "Registrar biopsias y citologías, numeración y filtros."),
            ("6. El informe", "Campos, plantillas, diagnósticos y anexos."),
            ("7. Emitir (Entregado)", "Cuándo se emite, qué exige y qué implica."),
            ("8. Imprimir y PDF", "Impresión y copia archivada."),
            ("9. Reportes", "Producción, pendientes y estadísticas."),
            ("10. Usuarios", "Altas, bajas y contraseñas (administrador)."),
            ("11. Respaldos", "Copias de la base (administrador)."),
            ("12. Cuarentena", "Anomalías del histórico (administrador)."),
            ("13. Preguntas frecuentes", "Problemas habituales y qué hacer."),
            ("14. Buenas prácticas", "Recomendaciones para el uso diario."),
            ("Glosario", "NB, NC, entregado, emisión y otros términos."),
        ],
        widths=[5.4, 11.0],
    )

    callout(
        doc,
        "Cómo leer este manual.",
        "Cada sección explica una pantalla y termina con lo que conviene revisar. "
        "Las palabras en negrita son botones o nombres que vas a ver en el sistema.",
        "info",
    )

    # ------------------------------------------------------------------ 1
    h1(doc, "1. Qué es este sistema")
    para(
        doc,
        "Becomarca es el sistema del laboratorio para registrar pacientes, cargar "
        "estudios, escribir informes, emitirlos, imprimirlos y consultar la producción. "
        "Reemplaza al programa anterior en Access: ahora se usa desde el navegador y la "
        "información queda en un servidor del laboratorio, no en un archivo suelto.",
    )
    h2(doc, "Qué se puede hacer")
    bullets(
        doc,
        [
            ("Pacientes:", "buscar por cédula, número de biopsia (NB) o de citología (NC), crear y corregir datos."),
            ("Estudios:", "registrar una biopsia o una citología con su número y su fecha."),
            ("Informes:", "escribir el informe con plantillas, diagnósticos y anexos."),
            ("Emisión:", "marcar el informe como entregado, generar el PDF y habilitar la impresión."),
            ("Reportes:", "ver producción por año, pendientes y productividad."),
            ("Respaldos:", "guardar una copia de la base desde la misma aplicación."),
        ],
    )

    h2(doc, "El flujo de todos los días")
    steps(
        doc,
        [
            ("Llega la muestra.", "Se busca el paciente; si no existe, se crea."),
            ("Se registra el estudio.", "Se elige biopsia o citología y el sistema propone el número."),
            ("Días después se escribe el informe.", "Macroscópico, microscópico y diagnóstico."),
            ("Se emite.", "El informe pasa a Entregado y se habilita la impresión."),
            ("Se imprime o se descarga el PDF.", "Queda una copia archivada con su huella de verificación."),
        ],
    )

    callout(
        doc,
        "Importante.",
        "“Entregado” significa que el informe está terminado y listo para imprimir, "
        "no que el paciente ya lo retiró.",
        "warn",
    )

    # ------------------------------------------------------------------ 2
    h1(doc, "2. Cómo entrar")
    steps(
        doc,
        [
            ("Abrí el navegador.", "Chrome, Edge o Firefox, en el equipo del laboratorio."),
            ("Escribí la dirección.", "http://laboratorio-cito-biop.test"),
            ("Poné tu usuario y contraseña.", "Te los da el administrador. El usuario es un correo."),
            ("Entrá.", "Si es la primera vez, pedile al administrador que te asigne una contraseña personal."),
        ],
    )
    para(
        doc,
        "Si el dominio no abre, probá con la dirección alternativa: "
        "http://localhost/laboratorio-cito-biop/public",
        size=9.5,
        color=GREY,
    )

    h2(doc, "Si no podés entrar")
    bullets(
        doc,
        [
            "Revisá que el correo esté bien escrito y que no tenga espacios.",
            "Las contraseñas distinguen mayúsculas de minúsculas.",
            "Si sigue sin entrar, pedile al administrador que te restablezca la contraseña.",
            "No compartas tu contraseña con otros usuarios: cada persona tiene su cuenta.",
        ],
    )

    callout(
        doc,
        "Seguridad.",
        "Al terminar el día, cerrá sesión (menú de tu nombre, arriba a la derecha). "
        "Especialmente si el equipo es compartido.",
        "info",
    )

    # ------------------------------------------------------------------ 3
    h1(doc, "3. La pantalla principal")
    para(
        doc,
        "Arriba está el menú con las secciones. Lo que ves depende de tu rol: el "
        "administrador además ve Cuarentena, Respaldos y Usuarios.",
    )
    table(
        doc,
        ["Menú", "Para qué sirve", "Quién lo ve"],
        [
            ("Pacientes", "Buscar, crear y editar pacientes", "Todos"),
            ("Estudios", "Listado y filtros de biopsias y citologías", "Todos"),
            ("Reportes", "Producción, pendientes y estadísticas", "Todos"),
            ("Cuarentena", "Anomalías del histórico para depurar", "Administrador"),
            ("Respaldos", "Generar y descargar copias de la base", "Administrador"),
            ("Usuarios", "Altas, bajas y contraseñas", "Administrador"),
        ],
        widths=[3.4, 8.2, 4.0],
    )

    # ------------------------------------------------------------------ 4
    h1(doc, "4. Pacientes")
    h2(doc, "Buscar un paciente")
    para(
        doc,
        "En Pacientes hay un buscador con tres botones: CI, NB y NC.",
    )
    bullets(
        doc,
        [
            ("CI:", "busca por cédula. Podés escribir solo una parte."),
            ("NB:", "busca por número de biopsia."),
            ("NC:", "busca por número de citología."),
        ],
    )
    para(
        doc,
        "El resultado muestra el nombre, la cédula y cuántos estudios tiene. "
        "Hacé clic en Ver para abrir la ficha.",
    )

    h2(doc, "Crear un paciente")
    steps(
        doc,
        [
            ("Buscá primero.", "Si ya existe, no lo crees de nuevo."),
            ("Clic en Nuevo paciente.", "Arriba a la derecha."),
            ("Completá los datos.", "Cédula, nombre, sexo, fecha de nacimiento o edad, teléfono."),
            ("Médico y procedencia.", "Si los tenés a mano; se pueden corregir después."),
            ("Guardá.", "El paciente queda creado y podés agregarle un estudio."),
        ],
    )

    callout(
        doc,
        "Evitá duplicados.",
        "El sistema no bloquea cédulas repetidas. Si no buscás antes, podés crear un "
        "paciente duplicado: buscá siempre primero y usá el registro que ya existe.",
        "warn",
    )

    h2(doc, "La ficha del paciente")
    para(
        doc,
        "Muestra los datos personales y la lista de estudios, del más reciente al más "
        "antiguo. Desde ahí se agrega un estudio nuevo o se edita el paciente.",
    )

    # ------------------------------------------------------------------ 5
    h1(doc, "5. Estudios: biopsias y citologías")
    h2(doc, "Registrar un estudio")
    steps(
        doc,
        [
            ("Abrí la ficha del paciente.", "Buscarlo por cédula."),
            ("Clic en Nuevo estudio.", "Elegí biopsia o citología."),
            ("Revisá el número propuesto.", "Biopsia: cuatro dígitos y el año. Citología: cinco dígitos y el año."),
            ("Completá la fecha de recepción.", "Y el médico que remite y la procedencia."),
            ("Guardá.", "El estudio queda En proceso."),
        ],
    )

    callout(
        doc,
        "El número manda.",
        "Si el frasco trae otro número, corregilo antes de guardar. El sistema no "
        "renumera los estudios viejos: respeta la numeración histórica del laboratorio.",
        "info",
    )

    h2(doc, "Buscar estudios")
    para(
        doc,
        "En Estudios podés filtrar por año, tipo (biopsia o citología) y estado "
        "(recibido, en proceso, entregado). El botón Limpiar quita los filtros.",
    )

    h2(doc, "Corregir datos del estudio")
    para(
        doc,
        "Si te equivocaste en el número, la fecha o el médico, entrá al estudio y usá "
        "Datos del estudio. Se puede corregir mientras el informe no esté emitido.",
    )

    # ------------------------------------------------------------------ 6
    h1(doc, "6. El informe")
    para(
        doc,
        "Desde el estudio, clic en Editar informe. Se abre el editor con los campos "
        "del tipo de estudio.",
    )

    h2(doc, "Biopsia")
    bullets(
        doc,
        [
            ("Material remitido:", "lo que llegó del quirófano o consultorio."),
            ("Descripción macroscópica:", "cómo se ve la muestra."),
            ("Diagnóstico microscópico:", "el diagnóstico definitivo."),
            ("Notas:", "opcional, para aclaraciones."),
        ],
    )

    h2(doc, "Citología")
    bullets(
        doc,
        [
            ("Descripción citológica:", "lo que se observa en la lámina."),
            ("Diagnóstico citológico:", "la conclusión."),
            ("Calidad de la muestra:", "adecuada, insuficiente, etc."),
            ("Índice hormonal y anexos:", "si corresponde."),
        ],
    )

    h2(doc, "Herramientas del editor")
    bullets(
        doc,
        [
            ("Plantillas:", "insertan un texto modelo que después ajustás. Sirven para no escribir siempre lo mismo."),
            ("Catálogo de diagnósticos:", "buscás el diagnóstico y lo agregás con un clic."),
            ("Anexos:", "textos cortos ya cargados (flora, etc.)."),
        ],
    )

    callout(
        doc,
        "Guardar no es emitir.",
        "Guardar deja el informe como borrador y podés volver después. Emitir es el "
        "paso siguiente y tiene consecuencias: bloquea el informe.",
        "ok",
    )

    # ------------------------------------------------------------------ 7
    h1(doc, "7. Emitir el informe (Entregado)")
    para(
        doc,
        "Cuando guardás un informe completo, el sistema lo emite automáticamente: "
        "cambia el estado a Entregado, sella la fecha, genera el PDF y habilita la impresión.",
    )

    h2(doc, "Qué tiene que estar completo")
    table(
        doc,
        ["Tipo", "Campos obligatorios para emitir"],
        [
            ("Biopsia", "Material remitido + descripción macroscópica + diagnóstico microscópico"),
            ("Citología", "Descripción citológica + diagnóstico citológico + calidad de la muestra"),
        ],
        widths=[3.4, 12.2],
    )
    para(
        doc,
        "Si falta alguno, el sistema no emite: el estudio queda En proceso y te avisa "
        "qué falta.",
        size=9.5,
        color=GREY,
    )

    h2(doc, "Qué pasa al emitir")
    bullets(
        doc,
        [
            "El estado pasa a Entregado y se guarda la fecha de emisión.",
            "Se genera una copia PDF en el servidor, con su huella de verificación.",
            "El informe queda bloqueado: ya no se puede editar.",
            "Se habilita el botón Imprimir y la descarga del PDF.",
        ],
    )

    callout(
        doc,
        "Si te equivocaste después de emitir.",
        "El informe emitido no se edita. Pedile al administrador que lo reabra: "
        "el sistema registra quién lo reabrió y cuándo. Después de corregir, se vuelve "
        "a emitir y se genera una nueva versión del PDF.",
        "warn",
    )

    # ------------------------------------------------------------------ 8
    h1(doc, "8. Imprimir y PDF")
    h2(doc, "Imprimir")
    steps(
        doc,
        [
            ("Abrí el estudio.", "Debe estar Entregado."),
            ("Clic en Imprimir.", "Se abre la vista con el formato del laboratorio."),
            ("Imprimí.", "Con el botón de imprimir o Ctrl+P."),
        ],
    )

    h2(doc, "PDF archivado")
    para(
        doc,
        "El botón PDF descarga la copia que el sistema guardó al emitir. Es la copia "
        "fiel del informe y sirve para enviar por correo o para respaldo.",
    )
    bullets(
        doc,
        [
            "Cada emisión genera una versión nueva.",
            "La copia queda en el servidor, no en la computadora.",
            "Si el botón PDF no aparece, el informe todavía no está emitido.",
        ],
    )

    # ------------------------------------------------------------------ 9
    h1(doc, "9. Reportes")
    para(
        doc,
        "En Reportes elegís el año y el tipo, y el sistema muestra la producción.",
    )
    bullets(
        doc,
        [
            ("Totales:", "cuántos estudios, cuántos entregados y cuántos en proceso."),
            ("Producción por mes:", "el detalle mes a mes."),
            ("Pendientes:", "estudios sin cerrar, agrupados por antigüedad (hasta 7 días, hasta 30, más de 30)."),
            ("Productividad por patólogo:", "cuántos informes hizo cada uno."),
            ("Procedencia y médicos:", "de dónde vienen los estudios."),
        ],
    )
    para(
        doc,
        "Los años sin estudios aparecen como “(sin datos)”. El botón Imprimir sirve "
        "para llevar el resumen en papel.",
        size=9.5,
        color=GREY,
    )

    # ------------------------------------------------------------------ 10
    h1(doc, "10. Usuarios (solo administrador)")
    para(
        doc,
        "En Usuarios se crean las cuentas del personal. Hay dos roles:",
    )
    table(
        doc,
        ["Rol", "Puede"],
        [
            ("Administrador", "Todo: operación diaria, reabrir informes, cuarentena, respaldos y usuarios."),
            ("Operador", "Pacientes, estudios, informes, emisión, impresión y reportes."),
        ],
        widths=[3.6, 12.0],
    )
    h2(doc, "Crear un usuario")
    steps(
        doc,
        [
            ("Usuarios → Nuevo usuario.", "Nombre, correo, rol y contraseña."),
            ("Contraseña inicial.", "Mínimo 8 caracteres. Se la das a la persona."),
            ("Guardá.", "Puede entrar de inmediato."),
        ],
    )
    bullets(
        doc,
        [
            "No se puede eliminar el propio usuario.",
            "No se puede cambiar el propio rol.",
            "No se puede eliminar al último administrador.",
            "Todo alta, cambio o baja queda registrado.",
        ],
    )

    # ------------------------------------------------------------------ 11
    h1(doc, "11. Respaldos (solo administrador)")
    steps(
        doc,
        [
            ("Menú Respaldos.", "Muestra los respaldos existentes con su fecha y tamaño."),
            ("Generar respaldo ahora.", "Tarda unos segundos."),
            ("Descargar.", "Guardá el archivo en un pendrive o en otro equipo."),
        ],
    )
    callout(
        doc,
        "Regla de oro.",
        "Un respaldo guardado en el mismo equipo no protege de una falla del disco. "
        "Copialo a otro medio y probá la restauración al menos una vez.",
        "warn",
    )

    # ------------------------------------------------------------------ 12
    h1(doc, "12. Cuarentena (solo administrador)")
    para(
        doc,
        "Cuando se migró el histórico de Access quedaron anomalías: números "
        "duplicados, pacientes sin estudio, fechas imposibles. No se borró nada: "
        "todo está en esta bandeja para revisarlo.",
    )
    bullets(
        doc,
        [
            "Cada fila indica el motivo y los datos originales.",
            "Se puede resolver con una nota, que queda registrada.",
            "No es urgente: sirve para depurar el histórico con el tiempo.",
        ],
    )

    # ------------------------------------------------------------------ 13
    h1(doc, "13. Preguntas frecuentes")
    table(
        doc,
        ["Situación", "Qué hacer"],
        [
            ("No puedo entrar", "Revisá el correo y la contraseña. Si sigue, pedí que te la restablezcan."),
            ("Aparece “419” al guardar", "Recargá la página con F5 y volvé a intentar."),
            ("No encuentro un estudio", "Revisá el año y el estado elegidos; probá buscar por número."),
            ("El botón PDF no funciona", "El informe no está emitido: debe estar Entregado."),
            ("El número está mal", "Entrá al estudio y usá Datos del estudio antes de emitir."),
            ("Necesito corregir un informe emitido", "Pedile al administrador que lo reabra."),
            ("El paciente ya existía", "Buscá por cédula y usá el registro existente."),
            ("No veo el menú Usuarios", "Tu usuario es Operador; esas opciones son del administrador."),
        ],
        widths=[5.2, 10.4],
    )

    # ------------------------------------------------------------------ 14
    h1(doc, "14. Buenas prácticas")
    bullets(
        doc,
        [
            "Buscá siempre el paciente antes de crearlo.",
            "Verificá el número de la muestra contra el frasco antes de emitir.",
            "Guardá el borrador si tenés que interrumpir; no pierdes lo escrito.",
            "No emitas hasta tener el diagnóstico completo.",
            "Cerrá sesión en equipos compartidos.",
            "No compartas tu contraseña ni la anotes en el monitor.",
            "Avisá al administrador si un dato no coincide con el papel.",
        ],
    )

    # ------------------------------------------------------------------ glosario
    h1(doc, "Glosario")
    table(
        doc,
        ["Término", "Significado"],
        [
            ("NB", "Número de biopsia. Cuatro dígitos y el año."),
            ("NC", "Número de citología. Cinco dígitos y el año."),
            ("Estudio", "Una biopsia o una citología de un paciente."),
            ("Informe", "El texto del diagnóstico de un estudio."),
            ("Emitir", "Cerrar el informe; pasa a Entregado y se bloquea."),
            ("Entregado", "Informe terminado y listo para imprimir."),
            ("PDF archivado", "Copia del informe guardada en el servidor al emitir."),
            ("Cuarentena", "Bandeja con las anomalías del histórico."),
        ],
        widths=[4.0, 11.6],
    )

    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(14)
    r = p.add_run("Ante cualquier duda, consultá con el administrador del sistema.")
    r.font.size = Pt(9.5)
    r.italic = True
    r.font.color.rgb = GREY
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER

    doc.save(OUT)
    print("OK", OUT)


if __name__ == "__main__":
    build()
