from docx import Document
from docx.shared import Pt, Cm, RGBColor, Inches
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_TAB_ALIGNMENT, WD_TAB_LEADER
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
import datetime, pathlib

doc = Document()
st = doc.styles['Normal']
st.font.name = 'Arial'
st.font.size = Pt(12)
st.paragraph_format.space_after = Pt(6)
st.paragraph_format.line_spacing = Pt(14)  # ~1.15
st.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
for s in doc.sections:
    s.top_margin=Cm(2); s.bottom_margin=Cm(2); s.left_margin=Cm(2.5); s.right_margin=Cm(2.5)
    s.header_distance=Cm(1); s.footer_distance=Cm(1)

def h(text, lvl):
    hd = doc.add_heading(level=lvl)
    r = hd.add_run(text)
    r.bold=True; r.font.name='Arial'
    r.font.size=Pt({1:14,2:12,3:11}.get(lvl,12))
    r.font.color.rgb=RGBColor(0x0F,0x17,0x2A)
    hd.paragraph_format.space_before=Pt(12); hd.paragraph_format.space_after=Pt(6)
    hd.alignment=WD_ALIGN_PARAGRAPH.LEFT
    return hd

def p(text, bold=False, italic=False, size=12, color=None, align=WD_ALIGN_PARAGRAPH.JUSTIFY, bullet=False, after=6):
    para = doc.add_paragraph(style='List Bullet' if bullet else 'Normal')
    r = para.add_run(text)
    r.bold=bold; r.italic=italic; r.font.name='Arial'; r.font.size=Pt(size)
    if color: r.font.color.rgb=RGBColor.from_string(color)
    para.alignment=align; para.paragraph_format.space_after=Pt(after)
    return para

def tbl(headers, rows, font_size=9):
    t = doc.add_table(rows=1, cols=len(headers))
    t.style='Light Grid Accent 1'; t.alignment=WD_TABLE_ALIGNMENT.CENTER; t.autofit=True
    for i,hh in enumerate(headers):
        c=t.rows[0].cells[i]; c.text=''
        rr=c.paragraphs[0].add_run(hh); rr.bold=True; rr.font.name='Arial'; rr.font.size=Pt(font_size); rr.font.color.rgb=RGBColor(0xFF,0xFF,0xFF)
        c.paragraphs[0].alignment=WD_ALIGN_PARAGRAPH.CENTER
        shd=OxmlElement('w:shd'); shd.set(qn('w:fill'),'0F172A'); c._tc.get_or_add_tcPr().append(shd)
    for row in rows:
        cs=t.add_row().cells
        for i,v in enumerate(row):
            cs[i].text=''; rr=cs[i].paragraphs[0].add_run(str(v)); rr.font.name='Arial'; rr.font.size=Pt(font_size)
            cs[i].paragraphs[0].alignment=WD_ALIGN_PARAGRAPH.LEFT if len(headers)>2 else WD_ALIGN_PARAGRAPH.JUSTIFY
    doc.add_paragraph().paragraph_format.space_after=Pt(4)
    return t

def code_block(title, snippet):
    qq=doc.add_paragraph(); rr=qq.add_run(title); rr.bold=True; rr.font.name='Arial'; rr.font.size=Pt(10); rr.font.color.rgb=RGBColor(0x0F,0x17,0x2A)
    qq.paragraph_format.space_after=Pt(2); qq.alignment=WD_ALIGN_PARAGRAPH.LEFT
    pp=doc.add_paragraph()
    pr=pp._p.get_or_add_pPr()
    shd=OxmlElement('w:shd'); shd.set(qn('w:val'),'clear'); shd.set(qn('w:color'),'auto'); shd.set(qn('w:fill'),'F1F5F9'); pr.append(shd)
    bdr=OxmlElement('w:pBdr')
    for e in ['top','left','bottom','right']:
        el=OxmlElement(f'w:{e}'); el.set(qn('w:val'),'single'); el.set(qn('w:sz'),'4'); el.set(qn('w:space'),'4'); el.set(qn('w:color'),'E2E8F0'); bdr.append(el)
    pr.append(bdr)
    r2=pp.add_run(snippet); r2.font.name='Consolas'; r2.font.size=Pt(8); r2.font.color.rgb=RGBColor(0x1E,0x29,0x3B)
    pp.paragraph_format.space_after=Pt(8)
    pp.paragraph_format.left_indent=Cm(0.3); pp.paragraph_format.right_indent=Cm(0.3)
    pp.alignment=WD_ALIGN_PARAGRAPH.LEFT

def cap(num, titulo, ruta):
    box = doc.add_paragraph(); box.alignment=WD_ALIGN_PARAGRAPH.CENTER
    pr = box._p.get_or_add_pPr()
    shd=OxmlElement('w:shd'); shd.set(qn('w:val'),'clear'); shd.set(qn('w:color'),'auto'); shd.set(qn('w:fill'),'FFF7F7'); pr.append(shd)
    bdr=OxmlElement('w:pBdr')
    for e in ['top','left','bottom','right']:
        el=OxmlElement(f'w:{e}'); el.set(qn('w:val'),'single'); el.set(qn('w:sz'),'3'); el.set(qn('w:space'),'3'); el.set(qn('w:color'),'DC2626'); bdr.append(el)
    pr.append(bdr)
    r=box.add_run(f"  CAPTURA {num} — {titulo}  "); r.bold=True; r.font.name='Arial'; r.font.size=Pt(9); r.font.color.rgb=RGBColor(0xDC,0x26,0x26)
    box.paragraph_format.space_after=Pt(1)
    qq=doc.add_paragraph(); qq.alignment=WD_ALIGN_PARAGRAPH.CENTER
    r=qq.add_run(f"Ruta: {ruta}  ·  Debe incluir fecha y hora del sistema en la captura (Win+Shift+S)."); r.font.name='Arial'; r.font.size=Pt(7); r.italic=True; r.font.color.rgb=RGBColor(0x64,0x74,0x8B)
    qq.paragraph_format.space_after=Pt(1)
    bb=doc.add_paragraph(); bb.alignment=WD_ALIGN_PARAGRAPH.CENTER
    r=bb.add_run("┌─────────────────────────────────────────┐\n│   PEGA AQUÍ TU CAPTURA (Insertar > Imagen)  │\n└─────────────────────────────────────────┘")
    r.font.name='Consolas'; r.font.size=Pt(7); r.font.color.rgb=RGBColor(0x94,0xA3,0xB8)
    bb.paragraph_format.space_after=Pt(8)

# ============ PORTADA ============
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Universidad Continental"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(10)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Facultad de Ingeniería  •  Escuela Profesional de Ingeniería de Sistemas e Informática"); r.font.name='Arial'; r.font.size=Pt(8); r.font.color.rgb=RGBColor(0x47,0x56,0x6B)
pp.paragraph_format.space_after=Pt(14)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("PRODUCTO ACADÉMICO — ENTREGABLE FINAL"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(12); r.font.color.rgb=RGBColor(0xDC,0x26,0x26)
pp.paragraph_format.space_after=Pt(4)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Asignatura: Ingeniería Web"); r.font.name='Arial'; r.font.size=Pt(9); r.font.color.rgb=RGBColor(0x64,0x74,0x8B)
pp.paragraph_format.space_after=Pt(16)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
pr=pp._p.get_or_add_pPr(); shd=OxmlElement('w:shd'); shd.set(qn('w:val'),'clear'); shd.set(qn('w:color'),'auto'); shd.set(qn('w:fill'),'0F172A'); pr.append(shd)
r=pp.add_run("Sistema Web para la Gestión Empresarial\ny Comercialización de Productos de Limpieza\nCCOLCAS"); r.font.name='Arial'; r.font.size=Pt(15); r.bold=True; r.font.color.rgb=RGBColor(0xFF,0xFF,0xFF)
pp.paragraph_format.space_before=Pt(10); pp.paragraph_format.space_after=Pt(10)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("CCOLCA'S — Inventario, Almacén y Ventas"); r.font.name='Arial'; r.font.size=Pt(9); r.italic=True; r.font.color.rgb=RGBColor(0x47,0x56,0x6B)
pp.paragraph_format.space_after=Pt(12)
tbl(["",""],[["Autor:","Suble Lima Denilson Rivaldo"],["Sede:","Cusco — 2026"],["Docente:","Christian Alonso Vega Cervantes"],["Repositorio:","https://github.com/75957473-design/Ccolcas.git"]], font_size=9)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER; pp.paragraph_format.space_before=Pt(10)
r=pp.add_run("Huancayo — Perú  •  2026"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(10)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run(datetime.datetime.now().strftime("%d/%m/%Y")); r.font.name='Arial'; r.font.size=Pt(7); r.font.color.rgb=RGBColor(0x94,0xA3,0xB8)
# footer numeracion
sect = doc.sections[0]
footer = sect.footer
fp = footer.paragraphs[0]; fp.alignment=WD_ALIGN_PARAGRAPH.CENTER
run = fp.add_run(); fldChar1 = OxmlElement('w:fldChar'); fldChar1.set(qn('w:fldCharType'),'begin'); run._r.append(fldChar1)
run2 = fp.add_run(); instr = OxmlElement('w:instrText'); instr.set(qn('xml:space'),'preserve'); instr.text=' PAGE '; run2._r.append(instr)
run3 = fp.add_run(); fldChar2 = OxmlElement('w:fldChar'); fldChar2.set(qn('w:fldCharType'),'end'); run3._r.append(fldChar2)
for rr in fp.runs: rr.font.name='Arial'; rr.font.size=Pt(7); rr.font.color.rgb=RGBColor(0x94,0xA3,0xB8)
doc.add_page_break()

# ============ INDICE PAG 2 ============
h("Índice",1)
entries = [
("1. Nombre del proyecto", "3"),
("2. Descripción del proyecto", "3"),
("   2.1. Descripción de la organización", "3"),
("   2.2. Problemática identificada", "3"),
("   2.3. Necesidad del negocio", "4"),
("3. Definición del producto del proyecto", "4"),
("   3.1. Naturaleza y propósito del sistema", "4"),
("   3.2. Módulos funcionales del sistema", "4"),
("   3.3. Delimitación del alcance", "5"),
("4. Sustentación: aplicativo nativo o híbrido", "5"),
("   4.1. Análisis respecto a dispositivos móviles", "5"),
("   4.2. Clasificación como Sistema Híbrido — Modelo DHARMA", "6"),
("5. Esquema de la arquitectura utilizada", "6"),
("   5.1. Patrón Modelo-Vista-Controlador (MVC)", "6"),
("   5.2. Stack tecnológico", "7"),
("6. Plan de trabajo", "7"),
("7. Análisis y diseño del proyecto", "8"),
("   7.1. Diagrama de casos de uso por módulo", "8"),
("   7.2. Diagrama de clases por módulo", "9"),
("   7.3. Mapa de navegación web", "9"),
("8. Interfaces de la aplicación web", "10"),
("9. Método, metodología y estrategia", "13"),
("   9.1. Metodología OOHDM", "13"),
("   9.2. Estrategia incremental", "13"),
("   9.3. Método de evaluación tripartito", "13"),
("10. Modelo de calidad", "14"),
("11. Pruebas de validación", "14"),
("12. Medidas de seguridad", "15"),
("13. Contramedidas de seguridad", "16"),
("14. Conclusiones", "16"),
("15. Anexos", "17"),
("   Anexo A — Código de cada módulo", "17"),
("   Anexo B — Código de conexión a la base de datos", "19"),
("   Anexo C — Capturas de pruebas por módulo", "20"),
("Referencias", "20"),
]
for title, pg in entries:
    para = doc.add_paragraph()
    para.paragraph_format.space_after=Pt(1)
    para.paragraph_format.tab_stops.add_tab_stop(Cm(16), WD_TAB_ALIGNMENT.RIGHT, WD_TAB_LEADER.DOTS)
    r = para.add_run(title); r.font.name='Arial'; r.font.size=Pt(9)
    if title.startswith("   "): r.font.color.rgb=RGBColor(0x47,0x56,0x6B)
    else: r.bold=True; r.font.color.rgb=RGBColor(0x0F,0x17,0x2A)
    r2 = para.add_run(f"\t{pg}"); r2.font.name='Arial'; r2.font.size=Pt(9); r2.font.color.rgb=RGBColor(0x64,0x74,0x8B)
p("Nota: La paginación es referencial y se ajusta tras la inserción de capturas con fecha y hora.", italic=True, size=8, color="94A3B8", align=WD_ALIGN_PARAGRAPH.CENTER)
doc.add_page_break()

# ============ 1 ============
h("1. Nombre del proyecto",1)
p("CCOLCA'S — Sistema Web de Gestión de Inventario, Almacén y Ventas.", bold=True, size=12, color="0F172A")
p("Denominación formal: Sistema Web para la Gestión Empresarial y Comercialización de Productos de Limpieza de CCOLCAS.", italic=True, size=12, color="334155")

# ============ 2 ============
h("2. Descripción del proyecto",1)
h("2.1. Descripción de la organización",2)
p("CCOLCAS es una empresa dedicada a la comercialización y distribución de productos de limpieza en la región del Cusco. Opera sin establecimiento comercial físico abierto al público y gestiona la totalidad de sus pedidos mediante mensajería (WhatsApp) y llamadas telefónicas. Confirmado el pedido, el personal prepara la mercadería y coordina el despacho.", size=12)
p("Para la gestión administrativa utilizaba un catálogo impreso y en PDF, complementado con registros manuales en papel y hojas de cálculo en Excel. Esta metodología ocasionaba dispersión de la información y dificultaba el seguimiento del stock y la consolidación de las operaciones.", size=12)
p("La estructura organizativa está conformada por seis perfiles:", size=12)
tbl(["Perfil","Función principal"],[
["Gerente","Supervisa la gestión global y consulta métricas de desempeño."],
["Subgerente","Apoya el control y la coordinación operativa de las áreas."],
["Contador","Administra costos, márgenes y fijación de precios de venta."],
["Encargado de Almacén","Registra entradas, salidas y transferencias de mercadería."],
["Vendedor","Atiende clientes, gestiona cotizaciones y formaliza pedidos."],
["Cliente","Explora el catálogo, consulta disponibilidad y realiza pedidos."],
])
h("2.2. Problemática identificada",2)
tbl(["Dimensión","Descripción"],[
["Dispersión de la información","Datos fragmentados entre Excel y registros en papel."],
["Desactualización del inventario","Movimientos anotados manualmente antes de su registro digital."],
["Consultas de stock complejas","Sin fuente centralizada para verificación en tiempo real."],
["Atención de pedidos no estandarizada","Sin trazabilidad del ciclo de vida del pedido por WhatsApp."],
["Fijación de precios manual","Cálculo independiente de costos, fletes y márgenes propenso a error."],
["Falta de presencia digital interactiva","Sin plataforma web propia para exhibir productos y recibir órdenes."],
])
h("2.3. Necesidad del negocio",2)
p("CCOLCAS requiere una solución web centralizada que integre catálogo, gestión de precios, control de inventario multialmacén, procesamiento de pedidos y registro de ventas, con acceso diferenciado por rol y un canal web interactivo para clientes finales.", size=12)

# ============ 3 ============
h("3. Definición del producto del proyecto",1)
h("3.1. Naturaleza y propósito del sistema",2)
p("El producto es una aplicación web interactiva que automatiza la gestión operativa y comercial de CCOLCAS. Permite administrar productos, categorías, inventarios multialmacén, precios, clientes, pedidos, comprobantes de venta y usuarios bajo control de acceso basado en roles (RBAC). Centraliza lo antes disperso y habilita la confirmación de pedidos 100% por web con seguimiento por estados.", size=12)
h("3.2. Módulos funcionales del sistema",2)
tbl(["Módulo","Descripción funcional"],[
["A. Gestión de Productos","Registro, actualización, consulta y desactivación de productos y categorías. Precio calculado automáticamente."],
["B. Gestión de Inventario","Control por almacén, kardex de entradas/salidas y transferencias entre almacenes con bloqueo transaccional."],
["C. Gestión de Precios","Definición restringida de costo de adquisición, flete y margen porcentual para el PVP."],
["D. Catálogo Web Interactivo","Búsqueda, filtros por categoría, detalle técnico y carrito de compras."],
["E. Gestión de Clientes","Perfiles, direcciones de entrega, DNI/RUC y datos de contacto."],
["F. Gestión de Pedidos","Flujo Pendiente → Confirmado → En Almacén → Enviado → Entregado/Cancelado con trazabilidad y control por rol."],
["G. Gestión de Ventas","Comprobantes (Boleta, Factura, Nota de Venta) con descuento automático de stock."],
["H. Usuarios y Roles","Administración de credenciales y asignación de siete perfiles."],
])
h("3.3. Delimitación del alcance",2)
tbl(["Alcance","Detalle"],[
["Incluido en la versión actual","Autenticación RBAC, catálogo público, carrito, checkout con validación de stock, flujo de pedidos, facturación interna, kardex multialmacén, transferencias, auditoría en bitácora y 168 pruebas."],
["Excluido para fases futuras","Pasarela de pago con tarjeta, facturación electrónica directa con SUNAT y contabilidad avanzada."],
])

# ============ 4 SUSTENTO NATIVO/HIBRIDO ============
h("4. Sustentación: aplicativo nativo o híbrido",1)
p("En atención a lo solicitado en el enunciado, se sustenta a continuación la clasificación del proyecto Implementado.", italic=True, size=11, color="475569")
h("4.1. Análisis respecto a dispositivos móviles (Nativa vs. Híbrida Mobile vs. Web)",2)
p("La solución no constituye una aplicación móvil nativa, por no requerir compilación específica para Android/iOS ni uso de SDKs de dispositivo, ni una aplicación móvil híbrida empaquetada en WebView (Cordova, Capacitor, Flutter híbrido). Corresponde técnicamente a una Aplicación Web Interactiva accesible mediante navegadores estándar bajo HTTP/HTTPS, con despliegue centralizado y compatibilidad multiplataforma. Esta elección se sustenta en los contenidos de la unidad sobre tipos de aplicaciones web: se priorizó alcance, mantenimiento único y disponibilidad inmediata sin distribución por stores, suficiente para catálogo, carrito y seguimiento de pedidos. La limitación inherente —dependencia de conectividad y acceso restringido a hardware del dispositivo— no es crítica para el dominio del negocio.", size=12)
h("4.2. Clasificación como Sistema Híbrido según el Modelo DHARMA",2)
p("Desde la perspectiva de arquitectura de software bajo el framework DHARMA (Discovering Hybrid Architectures by Modelling Actors), el sistema se clasifica conceptualmente como Sistema Híbrido, conforme a lo estudiado en la unidad de arquitecturas web:", size=12)
tbl(["Pilar DHARMA","Sustento en CCOLCAS"],[
["Composición heterogénea","Integra componentes COTS/FOSS diversos: Laravel 12 (PHP 8.2), MySQL, Vite, Tailwind CSS y lógica a medida."],
["Modelado i* (SD/SR)","Dependencias entre actores (Cliente, Vendedor, Almacenero, Contador, Administrador) y componentes desacoplados que resuelven metas específicas (stock crítico, precio, auditoría)."],
["Descomposición modular","Subcomponentes autónomos —pedidos, workflow de estados, observers de auditoría— que permiten desarrollo e integración incremental."],
])
p("En síntesis: para el usuario final es una aplicación web interactiva; arquitectónicamente es un sistema híbrido en sentido DHARMA por su composición heterogénea y diseño por actores.", bold=True, size=12, color="0F172A")

# ============ 5 ARQ ============
h("5. Esquema de la arquitectura utilizada",1)
h("5.1. Patrón Modelo-Vista-Controlador (MVC)",2)
p("Se adopta el patrón MVC provisto por Laravel 12 para separar responsabilidades:", size=12)
tbl(["Componente","Responsabilidad"],[
["Modelos (app/Models/*)","19 entidades con Eloquent (belongsTo, hasMany) y lógica de cálculo."],
["Controladores (app/Http/Controllers/*)","Validación, coordinación de transacciones y orquestación HTTP."],
["Vistas (resources/views/*)","Blade + Tailwind CSS, separadas en interfaz pública y panel administrativo."],
["Servicios / Observadores","PedidoWorkflowService (máquina de estados), AuditObserver (bitácora), RoleMiddleware (RBAC)."],
])
h("5.2. Stack tecnológico",2)
tbl(["Capa","Tecnología"],[
["Frontend","HTML5, CSS3, Tailwind CSS, JavaScript ES6, Blade, Vite"],
["Backend","PHP 8.2, Laravel 12"],
["Base de Datos","MySQL 8.0 (ccolcasdb) / SQLite (pruebas)"],
["Seguridad","Bcrypt rounds 12, CSRF tokens, Middleware RBAC"],
["Entorno","XAMPP (Apache/MySQL), Composer, Git, GitHub"],
])
p("Despliegue local: XAMPP ejecuta Apache y MySQL; el código reside en el repositorio https://github.com/75957473-design/Ccolcas.git. La base de producción es ccolcasdb; en pruebas se usa SQLite en memoria.", size=12)
cap("1", "Arquitectura MVC — estructura de carpetas", "VS Code: app/Models + resources/views + routes/web.php")

# ============ 6 ============
h("6. Plan de trabajo",1)
p("El proyecto se ejecutó en cuatro semanas (04/09/2026 – 01/10/2026):", size=12)
tbl(["Fase","Periodo","Entregables y actividades principales"],[
["Semana 1: Análisis","04/09 – 10/09","Levantamiento de procesos, identificación de problemas, perfiles de usuario, requerimientos y delimitación del alcance."],
["Semana 2: Diseño","11/09 – 17/09","Modelo ER (19 tablas), diagramas UML, maquetación de vistas y configuración del repositorio."],
["Semana 3: Construcción","18/09 – 24/09","Autenticación RBAC, catálogo, carrito, checkout, precios, multialmacén e historial de pedidos."],
["Semana 4: Integración y Cierre","25/09 – 01/10","Ventas, descuento de stock transaccional, auditoría con Observers, 168 pruebas y documentación final."],
])
cap("2", "Evidencia del plan — control de versiones y migraciones", "GitHub Commits + php artisan migrate:status en terminal")

# ============ 7 ANALISIS ============
h("7. Análisis y diseño del proyecto",1)
p("El análisis identificó la dispersión de información, la desactualización del inventario y la atención no estandarizada como restricciones reales. Se validaron requerimientos funcionales bajo criterios de viabilidad temporal (cuatro semanas) y tecnológica (XAMPP local, sin pagos en línea en v1). El diseño aplicó OOHDM (ver cap. 9) y se sustentó en técnicas de modelado UML vistas en la unidad de análisis y diseño.", size=12)
h("7.1. Diagrama de casos de uso por módulo",2)
p("El diagrama general delimita seis actores y ocho módulos. Para cumplir lo solicitado se presentan la vista general y el desglose por módulo (si no se dispone de uno por módulo, se emplea el general sustentado con la tabla siguiente, conforme a la rúbrica de definición de requerimientos).", size=12)
tbl(["Módulo","Actores y casos principales"],[
["A. Productos","Vendedor, Almacenero, Contador, Gerente: crear/editar/desactivar. Cliente: consultar en catálogo."],
["B. Inventario","Almacenero/Admin: entradas/salidas, ajustes y transferencias. Contador: consulta."],
["C. Precios","Contador/Admin: definir costo, flete y margen. Resto: consulta."],
["D. Catálogo","Cliente: buscar, filtrar, ver detalle y agregar al carrito."],
["E. Clientes","Vendedor/Admin: gestionar; Cliente: auto-registro."],
["F. Pedidos","Cliente: crear y seguir; Vendedor: confirmar/cancelar; Almacenero: preparar/enviar; Contador: cerrar."],
["G. Ventas","Vendedor/Contador/Admin: registrar comprobante y descontar stock."],
["H. Usuarios y Roles","Admin/Gerente: crear usuarios y asignar roles."],
])
cap("3", "Casos de uso — vista general (6 actores × 8 módulos)", "Diagrama general completo")
for idx, tit in enumerate(["A. Productos","B. Inventario","C. Precios","D. Catálogo","E. Clientes","F. Pedidos","G. Ventas","H. Usuarios y Roles"], start=4):
    cap(str(idx), f"Casos de uso — {tit}", "Diagrama por módulo o detalle del general + tabla superior")

h("7.2. Diagrama de clases por módulo",2)
p("El modelo de 19 entidades se implementa en app/Models con Eloquent. Relaciones principales: unidades_medida 1—* productos *—1 categorías; productos 1—* stock_por_almacen *—1 almacenes; roles 1—* usuarios 1—0..1 clientes 1—* pedidos 1—* detalle_pedidos *—1 productos; estados_pedido 1—* pedidos 1—0..1 ventas; pedidos 1—* historial_pedido; proveedores 1—* compras 1—* detalle_compras; tipos_movimiento 1—* movimientos_inventario; usuarios 1—* bitacora_cambios/historial_pedido.", size=12)
cap("12", "Diagrama de clases — 19 entidades (Eloquent)", "Diagrama de clases o ER generado con php artisan generate:erd")
tbl(["Entidad","Atributos clave","Comportamiento"],[
["Producto","código, nombre, costo_compra, costo_flete, margen, precio_venta","calcularPrecioVenta(), stockTotal(), esCritico()"],
["Pedido","cliente, estado, total, dirección","historial(), detalles(), venta()"],
["Usuario","nombre, rol, email, password hash","esAdmin(), esVendedor(), esCliente()"],
["HistorialPedido","pedido, estado anterior/nuevo, usuario, observación","trazabilidad del flujo"],
["BitacoraCambio","tabla, acción, datos previos/nuevos, usuario","auditoría vía Observer"],
])

h("7.3. Mapa de navegación web",2)
p("Se diseñó bajo OOHDM separando contextos navegacionales público y restringido por rol:", size=12)
tbl(["Zona","Nodos","Acceso"],[
["Público","Inicio (catálogo), búsqueda, carrito","Sin autenticación"],
["Cliente autenticado","Checkout, Mis pedidos, Detalle con seguimiento","auth + rol Cliente"],
["Acceso","Login, registro, logout","Público"],
["Admin — General","Dashboard, productos, categorías","Personal (no Cliente)"],
["Admin — Operaciones","Inventario, pedidos, ventas","Según matriz RBAC"],
["Admin — Gestión","Clientes, usuarios y roles","Usuarios: solo Admin/Gerente"],
])
cap("13", "Mapa de navegación web", "Diagrama de navegación con flujos Cliente vs Admin")

# ============ 8 INTERFACES ============
h("8. Interfaces de la aplicación web",1)
p("Las interfaces se construyeron con Blade y Tailwind CSS, con hojas de estilo y scripts separados por área (inicio.css, carrito.css, pedidos.css, admin.css). A continuación se ilustra cada interfaz del sistema. Cada captura debe incluir fecha y hora del sistema, conforme a lo indicado en el formato de presentación.", bold=True, size=12, color="DC2626")
interfaces = [
("8.1. Catálogo (/) — interfaz pública","Muestra buscador por nombre/descripción/código, filtro por categoría, badge del carrito y grilla de productos con imagen, nombre, categoría, descripción, precio y stock. Solo productos activos.","/ — welcome.blade.php"),
("8.2. Carrito (/carrito)","Tabla con producto, precio unitario, cantidad editable, subtotal, eliminación y resumen lateral del total. Estado en sesión.","/carrito"),
("8.3. Login y Registro (modal en /)","Dos pestañas: ingreso (email/contraseña) y registro de cliente (nombre, DNI/RUC, teléfono, email, contraseña). Registro transaccional Usuario + Cliente.","Modal en /"),
("8.4. Checkout (/checkout)","Dirección de entrega pre-llenada, tipo de comprobante (Boleta/Factura), observaciones, resumen y validación de stock/precio contra BD antes de crear el pedido.","/checkout (auth + carrito)"),
("8.5. Mis Pedidos y Detalle (/mi-cuenta/pedidos)","Listado con stepper por estados, badge de estado, tabla de productos y enlace a detalle con timeline auditable (historial_pedido) y venta asociada.","/mi-cuenta/pedidos y /mi-cuenta/pedidos/{id}"),
("8.6. Panel Administrativo (/admin/dashboard)","Dashboard con saludo, métricas y menú lateral. Layout layouts/admin.blade.php. Acceso denegado (403) para rol Cliente.","/admin/dashboard"),
("8.7. Productos (/admin/productos)","Listado paginado con búsqueda y filtros, CRUD, toggle activo/inactivo y fórmula de precio sugerida en el formulario.","/admin/productos"),
("8.8. Categorías e Inventario (/admin/categorias, /admin/inventario)","Categorías: CRUD + toggle. Inventario: stock por almacén (OK/Crítico/Agotado), últimos 20 movimientos, registro de movimientos, ajuste directo y transferencia entre almacenes.","/admin/categorias, /admin/inventario"),
("8.9. Pedidos — Administración (/admin/pedidos)","Listado con filtro por estado y buscador; detalle con stepper, timeline y formulario de cambio de estado filtrado por rol según workflow.","/admin/pedidos y /admin/pedidos/{pedido}"),
("8.10. Ventas (/admin/ventas)","Listado, creación desde pedido (comprobante, método de pago, monto) con descuento de stock transaccional y vista de detalle.","/admin/ventas"),
("8.11. Clientes (/admin/clientes)","Listado, detalle y edición de datos de entrega y contacto.","/admin/clientes"),
("8.12. Usuarios y Roles (/admin/usuarios)","Gestión restringida a Administrador/Gerente: alta, edición, toggle y asignación de siete roles.","/admin/usuarios"),
]
cnum=14
for tit, desc, ruta in interfaces:
    h(tit,2)
    p(desc, size=12)
    qq=doc.add_paragraph(); rr=qq.add_run(f"Ruta: {ruta}"); rr.font.name='Arial'; rr.font.size=Pt(8); rr.italic=True; qq.paragraph_format.space_after=Pt(1)
    cap(str(cnum), tit, f"Ruta: {ruta}")
    cnum+=1

# ============ 9 METODOLOGIA ============
h("9. Método, metodología y estrategia",1)
h("9.1. Metodología de diseño: OOHDM",2)
p("Se aplicó el Método de Diseño Hipermedia Orientado a Objetos (OOHDM), apropiado para aplicaciones web con zona pública y zona privada por roles, conforme a lo estudiado en la unidad de metodologías de diseño web. Se sustentó en:", size=12)
tbl(["Fase OOHDM","Aplicación en CCOLCAS"],[
["Diseño Conceptual","Modelo de 19 entidades interrelacionadas."],
["Diseño Navegacional","Contextos público (Catálogo → Carrito → Checkout) y restringidos por rol (panel admin)."],
["Diseño de Interfaz Abstracta (ADV)","Componentes Blade + Tailwind con CSS/JS por área y adaptabilidad responsive."],
["Implementación","Codificación MVC en Laravel 12 sobre XAMPP."],
])
h("9.2. Estrategia de desarrollo: Modelo Incremental",2)
p("Se adoptó el modelo incremental para reducir riesgos de integración y permitir validación progresiva, en línea con la rúbrica de diseño y desarrollo de sistemas:", size=12)
tbl(["Incremento","Contenido"],[
["Incremento 1","Estructura, XAMPP, repositorio Git y base de datos relacional."],
["Incremento 2","Autenticación, Bcrypt y RBAC."],
["Incremento 3","Catálogo, precios e inventario multialmacén."],
["Incremento 4","Pedidos, ventas, auditoría y pruebas."],
])
h("9.3. Método de evaluación tripartito",2)
tbl(["Método","Alcance"],[
["Heurística de Nielsen (10 principios)","Visibilidad del estado, prevención de errores y consistencia en flujos críticos."],
["Pruebas funcionales de reglas de negocio","Stock, permisos de precios y transacciones de inventario."],
["SUS (System Usability Scale)","Evaluación de usabilidad percibida con grupo operativo."],
])

# ============ 10 CALIDAD ============
h("10. Modelo de calidad",1)
p("Se adopta el estándar ISO/IEC 25010 (SQuaRE), visto en la unidad de calidad de software, focalizado en cuatro características pertinentes al dominio:", size=12)
tbl(["Característica","Cumplimiento en CCOLCAS"],[
["Adecuación funcional","Integridad transaccional con lockForUpdate, cálculo exacto de precios y 168 pruebas."],
["Seguridad","Bcrypt cost 12, CSRF, XSS sanitizado en Blade y variables aisladas en .env."],
["Usabilidad","Responsive, flujos por estados con stepper/timeline y mensajes claros."],
["Mantenibilidad","MVC modular, observers desacoplados y versionado en GitHub."],
])

# ============ 11 PRUEBAS ============
h("11. Pruebas de validación",1)
p("En atención al enunciado, se definen y sustentan las pruebas aplicables al proyecto, conforme a las unidades de validación y verificación:", size=12)
h("11.1. Pruebas unitarias",3)
p("Verifican unidades aisladas (cálculo de precio, stock crítico, transiciones). Se ejecutan con php artisan test sobre SQLite en memoria (168 tests, 335 aserciones). Sustento: validan que cada componente cumple su especificación antes de la integración.", size=12)
cap("26", "Pruebas unitarias — php artisan test", "Terminal: php artisan test (168 passed)")
h("11.2. Pruebas funcionales de reglas de negocio",3)
tbl(["Regla","Acción de prueba","Resultado esperado"],[
["Stock","Agregar más de lo disponible o checkout sin stock","Rechazo con mensaje \"Stock insuficiente\"."],
["Precios","Vendedor intenta editar precio","403 — solo Contador/Admin autorizado."],
["Venta","Registrar venta","Descuento atómico de stock y avance a Entregado."],
["Integridad de precio","Manipular precio en sesión","Revalidación contra BD en checkout."],
["RBAC","Cliente accede a /admin","403."],
])
h("11.3. Pruebas de integración (Feature)",3)
p("Flujos completos: catálogo solo con activos, búsqueda/filtro, autenticación por rol, carrito (agregar, actualizar cantidad, subtotal). Sustento: validan interacción entre componentes y cumplimiento de requerimientos de extremo a extremo.", size=12)
h("11.4. Evaluación heurística y SUS",3)
p("Heurística de Nielsen sobre navegación crítica y SUS con grupo operativo (8–12 participantes, umbral 68) para usabilidad percibida. Sustento: complementan la verificación técnica con validación de experiencia de usuario, según la rúbrica de uso de técnicas y metodologías.", size=12)
h("11.5. Pruebas complementarias",3)
p("Carga (ApacheBench/JMeter) sobre catálogo y checkout, seguridad con escáner OWASP ZAP y regresión tras cada incremento. Sustento: garantizan desempeño y no regresión, alineado al criterio de uso de herramientas modernas.", size=12)

# ============ 12 MEDIDAS ============
h("12. Medidas de seguridad",1)
p("Se sustentan las medidas implementadas para garantizar que el aplicativo no sea vulnerable, conforme a marcos OWASP, UCI y MADEJA estudiados:", size=12)
tbl(["Dimensión","Medida implementada","Evidencia en el código"],[
["Autenticidad","Hash Bcrypt 12, Hash::check, sin usuarios por defecto, CSRF en formularios","Usuario casts hashed, LoginController, BCRYPT_ROUNDS=12"],
["Autorización","Middleware RoleMiddleware (alias role), 403 para Cliente en /admin","bootstrap/app.php, routes/web.php"],
["Integridad","Validación estricta, Blade {{ }} anti-XSS, @csrf, paginación acotada","Form Requests, Blade, paginate()"],
["Confidencialidad","Mensajes genéricos, sin exposición de IDs sensibles, APP_DEBUG=false en producción","Manejo de errores, .env"],
["Trazabilidad","Observer → bitacora_cambios (datos previos/nuevos, usuario, fecha) + historial_pedido","AuditObserver, AppServiceProvider"],
["Disponibilidad","Transacciones con lockForUpdate en stock","PedidoController@store, InventarioController, VentaController@store"],
])
h("Cobertura OWASP Top 10",3)
tbl(["Riesgo OWASP","Mitigación en CCOLCAS"],[
["A01 Inyección","Eloquent parametrizado, sin SQL crudo."],
["A02 XSS","Escape en Blade y validación por listas blancas."],
["A03 Pérdida de autenticación","Bcrypt 12, sesión segura e invalidación al logout."],
["A04 IDOR","Verificación de existencia y autorización por rol."],
["A05 CSRF","@csrf en todos los formularios."],
["A06 Configuración insegura",".env aislado, APP_DEBUG=false en producción."],
["A07 Almacenamiento inseguro","Solo hash Bcrypt, nunca texto plano."],
["A08 Falta de control de acceso","Middleware role en grupo /admin."],
["A09 Comunicación insegura","Preparado para HTTPS (ver contramedidas)."],
["A10 Redirecciones abiertas","Solo route() internas."],
])

# ============ 13 CONTRAMEDIDAS ============
h("13. Contramedidas de seguridad",1)
p("Se indican contramedidas adicionales a implementar para elevar la postura de seguridad en producción, priorizadas según impacto y esfuerzo:", size=12)
tbl(["Contramedida","Prioridad","Implementación en CCOLCAS"],[
["HTTPS con certificado (Let's Encrypt) + HSTS","Alta","APP_URL https y forzado de TLS."],
["Autenticación de doble factor (2FA) para Admin/Gerente","Media-Alta","Fortify/Google2FA."],
["Rate limiting + CAPTCHA en login","Alta","throttle y ReCAPTCHA."],
["Cabeceras de seguridad (CSP, HSTS, X-Frame)","Alta","Middleware de cabeceras."],
["Respaldos diarios + WAF/IDS","Alta","Dump de ccolcasdb y firewall."],
["Política de contraseñas (12+, rotación, retardo)","Media","Validación y retardo ante fallos."],
["Expiración por inactividad (15 min)","Media","SESSION_LIFETIME 15."],
["Auditoría externa y sello de confianza","Futura","Evaluación por tercero y SLA."],
])
p("Recomendación: implementar en producción, como mínimo, HTTPS, rate limiting, cabeceras, respaldos y expiración por inactividad; 2FA y auditoría externa en el siguiente incremento.", italic=True, size=12)

# ============ 14 CONCLUSIONES ============
h("14. Conclusiones",1)
conclusiones = [
"El sistema centraliza catálogo, precios, inventario multialmacén, pedidos y ventas, resolviendo la dispersión de información y la desactualización del stock identificadas en el análisis.",
"La metodología OOHDM y la estrategia incremental permitieron una arquitectura MVC escalable y mantenible, alineada a ISO/IEC 25010 y validada progresivamente contra los requerimientos.",
"La clasificación como aplicación web interactiva con arquitectura híbrida (DHARMA) sustenta la composición heterogénea y el diseño por actores adoptados.",
"Las 168 pruebas y la cobertura de OWASP Top 10 avalan la solidez funcional y la seguridad por roles; las contramedidas propuestas completan la preparación para producción.",
]
for i, txt in enumerate(conclusiones, 1):
    p(f"{i}. {txt}", size=12, after=4)

# ============ 15 ANEXOS ============
h("15. Anexos",1)
p("Toda evidencia se presenta con fecha y hora del sistema, conforme al formato de presentación.", bold=True, size=12, color="DC2626")

h("Anexo A — Código de cada módulo",2)
p("Se presentan fragmentos representativos; el código completo está en el repositorio.", italic=True, size=10, color="64748B")

code_block("A.1. Productos — cálculo de precio (app/Models/Producto.php)", """public function calcularPrecioVenta(): float
{
    $costoTotal = $this->costo_compra + $this->costo_flete;
    return round($costoTotal * (1 + ($this->margen_ganancia / 100)), 2);
}""")

code_block("A.2. Inventario — transferencia multialmacén (InventarioController@transferir)", """DB::transaction(function () use ($data) {
    $origen = StockPorAlmacen::where('producto_id',$data['producto_id'])
        ->where('almacen_id',$data['almacen_origen_id'])->lockForUpdate()->first();
    if ($data['cantidad'] > $origen->cantidad) throw new \\Exception("Stock insuficiente");
    $origen->decrement('cantidad', $data['cantidad']);
    $destino = StockPorAlmacen::firstOrCreate(
        ['producto_id'=>$data['producto_id'],'almacen_id'=>$data['almacen_destino_id']],['cantidad'=>0]
    );
    $destino->increment('cantidad', $data['cantidad']);
    // Dos movimientos: Salida por Transferencia y Entrada por Transferencia
});""")

code_block("A.3. Pedidos — workflow por rol (PedidoWorkflowService)", """'Pendiente' => ['Confirmado','Cancelado'],
'Confirmado' => ['En Almacén','Cancelado'],
// ROLE_FOR_TARGET['Confirmado'] = ['Vendedor','Administrador','Gerente','Subgerente']
public static function puedeTransitar(Pedido $p, string $dest, string $rol): bool { ... }
PedidoWorkflowService::transitar($pedido, $destino, Auth::id());
HistorialPedido::create([...]);""")

code_block("A.4. Auditoría — Observer (AuditObserver)", """BitacoraCambio::create([
    'usuario_id'=>Auth::id(), 'tabla_afectada'=>$model->getTable(),
    'accion'=>$accion, 'datos_previos'=>json_encode($prev), 'datos_nuevos'=>json_encode($next),
    'fecha'=>now()
]);
// AppServiceProvider::boot(): Producto::observe(AuditObserver::class); Pedido::observe(...); Venta::observe(...);""")

code_block("A.5. Seguridad — RBAC (RoleMiddleware)", """// bootstrap/app.php: alias ['role'=>RoleMiddleware::class]
// routes/web.php: prefix('admin')->middleware('role:Administrador,Gerente,...')
// RoleMiddleware: if (!in_array($rol->nombre,$roles)) abort(403);""")

code_block("A.6. Ventas — descuento de stock (VentaController@store)", """DB::transaction(function() use ($pedido) {
    $venta = Venta::create([...]);
    foreach ($pedido->detalles as $d) {
        StockPorAlmacen::where('producto_id',$d->producto_id)->lockForUpdate()->first()
            ?->decrement('cantidad',$d->cantidad);
        MovimientoInventario::create([...]);
    }
});""")

h("Anexo B — Código de conexión a la base de datos",2)
code_block(".env", """APP_NAME=CCOLCAS
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ccolcasdb
DB_USERNAME=root
DB_PASSWORD=
BCRYPT_ROUNDS=12""")
code_block("config/database.php + config/auth.php", """'mysql' => ['host'=>env('DB_HOST'),'database'=>env('DB_DATABASE','ccolcasdb')],
'providers'=>['users'=>['driver'=>'eloquent','model'=>App\\Models\\Usuario::class]] // tabla usuarios""")
cap("27", "Anexo B — phpMyAdmin con ccolcasdb", "http://localhost/phpmyadmin — 19 tablas")
cap("28", "Anexo B — .env y config", "VS Code: .env + config/database.php")

h("Anexo C — Capturas de pruebas por módulo",2)
p("Cada captura debe mostrar fecha y hora del sistema (barra de tareas o comando date). Incluir Win+Shift+S a pantalla completa.", bold=True, size=12, color="DC2626")
tbl(["Anexo","Módulo probado","Comando / Ruta"],[
["C.1","Catálogo","/ + /catalogo/buscar — php artisan test --filter=CatalogoFeature"],
["C.2","Carrito","/carrito — agregar/actualizar/eliminar"],
["C.3","Autenticación","Modal login — php artisan test --filter=LoginFeature"],
["C.4","Checkout / Pedidos","/checkout → /mi-cuenta/pedidos"],
["C.5","Inventario","/admin/inventario — movimiento/ajuste/transferencia"],
["C.6","Productos y Precios","/admin/productos — CRUD + fórmula"],
["C.7","Workflow de pedidos","/admin/pedidos/{id} — cambio por rol"],
["C.8","Ventas","/admin/ventas — registro y descuento"],
["C.9","RBAC","/admin/dashboard como Cliente (403)"],
["C.10","Suite global","php artisan test + npm run build"],
])
for num, tit in [("C.1","Catálogo"),("C.2","Carrito"),("C.3","Autenticación"),("C.4","Checkout → Pedido"),("C.5","Inventario"),("C.6","Productos/Precios"),("C.7","Workflow pedidos"),("C.8","Ventas"),("C.9","RBAC 403"),("C.10","Suite global 168 tests")]:
    cap(num, tit, "Ver tabla superior — con fecha y hora")

# ============ REF ============
h("Referencias",1)
refs=[
"DHARMA — Discovering Hybrid Architectures by Modelling Actors (i* SD/SR, ETAPATELECOM).",
"MADEJA — Junta de Andalucía. Conceptos de Seguridad en Aplicaciones Web.",
"Universidad de las Ciencias Informáticas (Cuba). Requisitos de Seguridad — RNFS 1–37.",
"INCIBE. Protección de la Página Web: Políticas PYME.",
"ISO/IEC 25010 SQuaRE — Modelo de calidad.",
"OWASP Top 10 2021.",
"Documentación oficial de Laravel 12.",
]
for i,r in enumerate(refs,1):
    qq=doc.add_paragraph(); rr=qq.add_run(f"[{i}] {r}"); rr.font.name='Arial'; rr.font.size=Pt(9); rr.font.color.rgb=RGBColor(0x47,0x56,0x6B); qq.paragraph_format.space_after=Pt(2)

pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER; pp.paragraph_format.space_before=Pt(14)
r=pp.add_run("— Fin del Entregable Final —  CCOLCA'S  •  Suble Lima Denilson Rivaldo  •  Ingeniería Web  •  Cusco 2026 —"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(9)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Repositorio: https://github.com/75957473-design/Ccolcas.git  •  Arial 12  •  Archivo: PA2_Suble_Lima_Denilson_Rivaldo.docx"); r.font.name='Arial'; r.font.size=Pt(7); r.italic=True; r.font.color.rgb=RGBColor(0x64,0x74,0x8B)

out = r"docs/IngWebEntregabes/EntregableFinal_Suble_Lima_Denilson_Rivaldo.docx"
p2 = pathlib.Path(out)
if p2.exists():
    try: p2.unlink()
    except PermissionError: out = r"docs/IngWebEntregabes/EntregableFinal_Suble_Lima_Denilson_Rivaldo_NUEVO.docx"
doc.save(out)
print(f"Guardado en {out}")
print(f"Parrafos: {len(doc.paragraphs)}  Tablas: {len(doc.tables)}")
