from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
import datetime

doc = Document()
style = doc.styles['Normal']
style.font.name = 'Arial'
style.font.size = Pt(12)
style.paragraph_format.space_after = Pt(6)
style.paragraph_format.line_spacing = 1.15

for s in doc.sections:
    s.top_margin = Cm(2); s.bottom_margin = Cm(2)
    s.left_margin = Cm(2.5); s.right_margin = Cm(2.5)

def h(text, level):
    hd = doc.add_heading(level=level)
    r = hd.add_run(text)
    r.bold=True; r.font.name='Arial'
    r.font.size = Pt({1:16,2:13,3:11}.get(level,12))
    r.font.color.rgb = RGBColor(0x0F,0x17,0x2A)
    hd.paragraph_format.space_before = Pt(10)
    hd.paragraph_format.space_after = Pt(6)
    return hd

def p(text, bold=False, italic=False, size=11, color=None, align=WD_ALIGN_PARAGRAPH.JUSTIFY, bullet=False, after=6):
    para = doc.add_paragraph(style='List Bullet' if bullet else 'Normal')
    run = para.add_run(text)
    run.bold=bold; run.italic=italic; run.font.name='Arial'; run.font.size=Pt(size)
    if color: run.font.color.rgb = RGBColor.from_string(color)
    para.alignment = align
    para.paragraph_format.space_after = Pt(after)
    para.paragraph_format.line_spacing = 1.15
    return para

def add_table(headers, rows):
    t = doc.add_table(rows=1, cols=len(headers))
    t.style='Light Grid Accent 1'
    t.alignment = WD_TABLE_ALIGNMENT.CENTER
    t.autofit = True
    for i,hh in enumerate(headers):
        c=t.rows[0].cells[i]
        c.text=''
        rr=c.paragraphs[0].add_run(hh)
        rr.bold=True; rr.font.name='Arial'; rr.font.size=Pt(9); rr.font.color.rgb=RGBColor(0xFF,0xFF,0xFF)
        c.paragraphs[0].alignment=WD_ALIGN_PARAGRAPH.CENTER
        shd=OxmlElement('w:shd'); shd.set(qn('w:fill'),'0F172A')
        c._tc.get_or_add_tcPr().append(shd)
    for row in rows:
        cells=t.add_row().cells
        for i,val in enumerate(row):
            cells[i].text=''
            rr=cells[i].paragraphs[0].add_run(str(val))
            rr.font.name='Arial'; rr.font.size=Pt(9)
    doc.add_paragraph().paragraph_format.space_after=Pt(6)
    return t

def cap(titulo, ruta):
    para = doc.add_paragraph()
    para.alignment=WD_ALIGN_PARAGRAPH.CENTER
    r=para.add_run(f"[CAPTURA — Reemplazar: {titulo}]")
    r.bold=True; r.font.name='Arial'; r.font.size=Pt(9); r.font.color.rgb=RGBColor(0xDC,0x26,0x26)
    para.paragraph_format.space_after=Pt(1)
    q=doc.add_paragraph()
    q.alignment=WD_ALIGN_PARAGRAPH.CENTER
    rr=q.add_run(f"Insertar > Imagenes > {ruta}  (debe verse fecha/hora de tu PC)")
    rr.italic=True; rr.font.name='Arial'; rr.font.size=Pt(8); rr.font.color.rgb=RGBColor(0x64,0x74,0x8B)
    q.paragraph_format.space_after=Pt(1)
    b=doc.add_paragraph()
    b.alignment=WD_ALIGN_PARAGRAPH.CENTER
    rb=b.add_run("┌──────────────────────────────────────────────┐\n│        PEGAR CAPTURA AQUI  16:9               │\n└──────────────────────────────────────────────┘")
    rb.font.name='Consolas'; rb.font.size=Pt(8); rb.font.color.rgb=RGBColor(0x94,0xA3,0xB8)
    b.paragraph_format.space_after=Pt(10)

def code(title, snippet):
    q=doc.add_paragraph()
    rr=q.add_run(title); rr.bold=True; rr.font.name='Arial'; rr.font.size=Pt(9); rr.font.color.rgb=RGBColor(0x0F,0x17,0x2A)
    q.paragraph_format.space_after=Pt(2)
    pp=doc.add_paragraph()
    ppPr=pp._p.get_or_add_pPr()
    shd=OxmlElement('w:shd'); shd.set(qn('w:val'),'clear'); shd.set(qn('w:color'),'auto'); shd.set(qn('w:fill'),'F1F5F9')
    ppPr.append(shd)
    r2=pp.add_run(snippet)
    r2.font.name='Consolas'; r2.font.size=Pt(7); r2.font.color.rgb=RGBColor(0x1E,0x29,0x3B)
    pp.paragraph_format.space_after=Pt(8)

# PORTADA
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Año de la Esperanza y el Fortalecimiento de la Democracia"); r.font.name='Arial'; r.font.size=Pt(9); r.italic=True; r.font.color.rgb=RGBColor(0x64,0x74,0x8B)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Facultad de Ingenieria  •  Escuela Academica de Sistemas e Informatica"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(10); r.font.color.rgb=RGBColor(0x0F,0x17,0x2A)
pp.paragraph_format.space_after=Pt(14)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("PRODUCTO ACADEMICO — ENTREGABLE FINAL"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(12); r.font.color.rgb=RGBColor(0xFF,0x47,0x47)
pp.paragraph_format.space_after=Pt(4)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Asignatura: Ingenieria Web"); r.font.name='Arial'; r.font.size=Pt(9); r.font.color.rgb=RGBColor(0x64,0x74,0x8B)
pp.paragraph_format.space_after=Pt(16)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
ppPr=pp._p.get_or_add_pPr()
shd=OxmlElement('w:shd'); shd.set(qn('w:val'),'clear'); shd.set(qn('w:color'),'auto'); shd.set(qn('w:fill'),'0F172A'); ppPr.append(shd)
r=pp.add_run("Sistema Web para la Gestion Empresarial\ny Comercializacion de Productos de Limpieza\nCCOLCAS"); r.font.name='Arial'; r.font.size=Pt(15); r.bold=True; r.font.color.rgb=RGBColor(0xFF,0xFF,0xFF)
pp.paragraph_format.space_before=Pt(10); pp.paragraph_format.space_after=Pt(10)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("CCOLCA'S — Gestion de Inventario, Almacen y Ventas  •  Laravel 12  •  PHP 8.2  •  MySQL (ccolcasdb)"); r.font.name='Arial'; r.font.size=Pt(8); r.italic=True; r.font.color.rgb=RGBColor(0x64,0x74,0x8B)
pp.paragraph_format.space_after=Pt(18)
add_table(["",""],[["DOCENTE:","Christian Alonso Vega Cervantes"],["AUTOR:","Suble Lima Denilson Rivaldo"],["REPOSITORIO:","https://github.com/75957473-design/Colcas.git"],["STACK:","Laravel 12, PHP 8.2, MySQL, Vite 7, Tailwind 4, Blade"]])
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER; pp.paragraph_format.space_before=Pt(14)
r=pp.add_run("HUANCAYO — PERU  •  Sede Cusco  •  2026"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(10)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Entregable Final (integra PA1 + PA3 + ContenidoEntregableFinal)  •  Arial 12  •  Generado "+datetime.datetime.now().strftime("%d/%m/%Y %H:%M")); r.font.name='Arial'; r.font.size=Pt(7); r.italic=True; r.font.color.rgb=RGBColor(0xDC,0x26,0x26)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Reemplazar todos los bloques [CAPTURA] con imagenes reales con fecha/hora visible antes de subir al aula virtual."); r.font.name='Arial'; r.font.size=Pt(7); r.italic=True; r.font.color.rgb=RGBColor(0xDC,0x26,0x26)
doc.add_page_break()

# INDICE
h("Indice",1)
items=["1. Nombre del proyecto","2. Descripcion del proyecto","3. Definicion del producto","4. Aplicativo nativo vs hibrido (sustento DHARMA)","5. Esquema de arquitectura","6. Plan de trabajo (4 semanas)","7. Analisis y diseno","8. Diagrama Casos de Uso por modulo","9. Diagrama de Clases","10. Mapa de Navegacion Web","11. Procesos del negocio (Secuencias)","12. Interfaces de la aplicacion (capturas)","13. Metodo, metodologia y estrategia (OOHDM, Incremental, Tripartita)","14. Modelo de calidad ISO/IEC 25010","15. Pruebas de validacion","16. Medidas de seguridad (MADEJA/UCI/OWASP)","17. Contramedidas de seguridad (INCIBE/MADEJA)","18. Conclusiones","19. Referencias","20. Anexos (Codigo + Conexion BD + Capturas pruebas)"]
for it in items:
    q=doc.add_paragraph(); rr=q.add_run(it); rr.font.name='Arial'; rr.font.size=Pt(9); q.paragraph_format.space_after=Pt(1)
doc.add_page_break()

# 1
h("1. Nombre del proyecto",1)
p("CCOLCA'S — Sistema Web de Gestion de Inventario, Almacen y Ventas para la Comercializacion de Productos de Limpieza.", bold=True, size=12, color="0F172A", after=4)
p("Nombre oficial PA1 §1 y PA3 Portada: Sistema Web para la Gestion Empresarial y Comercializacion de Productos de Limpieza de CCOLCAS. Sede Cusco, 2026. Docente Christian Alonso Vega Cervantes. Autor Suble Lima Denilson Rivaldo.", size=10, color="475569")

# 2
h("2. Descripcion del proyecto",1)
h("2.1 Descripcion de la organizacion (PA1 §2.1)",2)
p("CCOLCAS distribuye y comercializa productos de limpieza en Cusco. Sin tienda fisica de atencion directa, vende por WhatsApp y llamadas, preparando y enviando luego. Usa catalogo fisico/PDF y gestion parcial en Excel y registros fisicos, con informacion dispersa y dificil de actualizar.", size=10)
add_table(["Rol","Funcion principal (PA1 §3.3)"],[["Gerente","Supervisar operaciones y consultar informacion."],["Subgerente","Apoyar administracion y supervision."],["Contador","Administrar precios y operaciones comerciales."],["Encargado de almacen","Registrar entradas/salidas y controlar stock."],["Vendedor","Gestionar clientes, pedidos y ventas."],["Cliente","Consultar catalogo y realizar pedidos web."]])
h("2.2 Situacion actual (PA1 §2.2)",2)
p("Cliente consulta por WhatsApp/llamada, vendedor verifica stock/precio, cliente confirma, se comunica a almacen para preparar y coordinar envio. Inventario en registros fisicos comunicado luego al contador para Excel. Precios los define contador (costo adquisicion + flete Cusco + margen + impuestos). Dependencia entre registros y comunicacion genera retrasos.", size=10)
h("2.3 Problemas identificados (PA1 §2.3)",2)
for t in ["Informacion dispersa (Excel + fisico).","Actualizacion tardia del inventario.","Dificultad para consultar stock.","Ventas poco organizadas (sin seguimiento).","Administracion manual de precios.","Falta de integracion entre areas.","Limitada presencia web."]:
    p(t, bullet=True, size=10, after=2)
h("2.4 Necesidad (PA1 §2.4)",2)
p("Centralizar en plataforma web productos, precios, inventario multialmacen, clientes, pedidos y ventas, con catalogo digital donde cliente consulte y registre pedidos organizadamente. Flujo actual exigido: cliente confirma via web y cada rol avanza el pedido donde le toca (vendedor confirma, almacen prepara/envia, contador registra venta), con trazabilidad visible.", size=10)

# 3
h("3. Definicion del producto del proyecto",1)
h("3.1 Definicion (PA1 §3.1 / PA3)",2)
p("Aplicacion web interactiva (PA1 §6.1) que centraliza informacion y facilita administracion de productos, categorias, precios, inventario, clientes, pedidos y ventas, con catalogo web. Desarrollada en Laravel 12 (PHP 8.2) sobre MySQL (ccolcasdb) en XAMPP, con RBAC para 7 roles (Administrador, Gerente, Subgerente, Contador, Almacenero, Vendedor, Cliente) y BCRYPT_ROUNDS=12.", size=10)
h("3.2 Objetivo (PA1 §3.2)",2)
p("Mejorar gestion comercial y operativa centralizando productos, precios, inventario, clientes, pedidos y ventas, facilitando catalogo y pedidos por Internet.", size=10, italic=True)
h("3.3 Usuarios (PA1 §3.3)",2)
p("Ver tabla §2.1. Cada rol accede solo a funciones permitidas (RoleMiddleware alias 'role' en bootstrap/app.php; Cliente excluido de /admin/* con 403).", size=10)
h("3.4 Modulos A–H (PA1 §3.4)",2)
add_table(["Modulo","Funciones"],
[["A. Productos","Registrar/modificar/consultar/desactivar; categorias; caracteristicas. Formula precio_venta=(costo_compra+costo_flete)*(1+margen%)."],
["B. Inventario","Entradas/salidas, stock total/por almacen, ajuste, transferencia multialmacen, historial movimientos."],
["C. Precios","Registro/actualizacion por autorizados (Contador/Admin)."],
["D. Catalogo web","Visualizar, caracteristicas, precios, buscar, filtrar categoria, anadir a pedido."],
["E. Clientes","DNI/RUC, nombre/razon, telefono, direccion entrega, persona recepcion."],
["F. Pedidos","Registrar, consultar, detalle, estado, entrega y seguimiento (cliente confirma web)."],
["G. Ventas","Registrar/consultar ventas vinculadas a pedido/cliente/productos."],
["H. Usuarios/Roles","Cuentas y permisos por rol."]])
h("3.5 Alcance (PA1 §4)",2)
p("Incluido v1 (4 semanas): usuarios, roles/permisos, productos, categorias, precios, inventario (entradas/salidas/stock), catalogo, busqueda, clientes, pedidos, ventas, entrega, control por rol.", size=10)
p("No incluido v1: pagos en linea, pasarelas, modulo/reportes contables especializados, facturacion electronica (etapa 2 con servicios externos).", size=10, color="DC2626")
p("Futuro: facturacion electronica, pagos en linea y reportes segun necesidad.", size=10)
h("3.6 Beneficios (PA1 §5)",2)
p("Empresa: centralizar, reducir fisico, mejorar inventario/precios, organizar pedidos/ventas, info para decisiones, presencia web.", bullet=True, size=10, after=2)
p("Personal: acceso rapido, menos duplicidad, menos tiempo consultando stock, control entradas/salidas, pedidos organizados, acceso por rol.", bullet=True, size=10, after=2)
p("Clientes: catalogo online, caracteristicas/precios, busqueda rapida, pedidos organizados, datos entrega registrados.", bullet=True, size=10, after=2)

# 4
h("4. Sustento: ¿Aplicativo nativo o hibrido? (ContenidoEntregableFinal + DHARMA)",1)
p("Respuesta: CCOLCAS es Aplicacion Web Interactiva (PA1 §6.1) —no nativa ni hibrida movil— y a la vez es sistema hibrido en sentido DHARMA (arquitectura por actores heterogeneos).", bold=True, size=10, color="0F172A")
h("4.1 Nativo vs hibrido vs web (movil)",2)
p("Nativa: app instalada compilada para un SO (Android/Kotlin, iOS/Swift), acceso total hardware, distribuida por stores. CCOLCAS no es nativa: no hay APK/IPA ni SDK nativo.", bullet=True, size=10, after=2)
p("Hibrida movil: contenedor WebView + puente nativo (Cordova/Capacitor, Flutter/React Native). CCOLCAS no usa WebView ni empaquetado.", bullet=True, size=10, after=2)
p("Web interactiva (nuestro caso): cliente HTML/CSS/JS + servidor PHP/Laravel + MySQL, accesible por navegador (127.0.0.1:8000 o localhost/Ccolcas/public via XAMPP). PA1 §6.1. Ventaja: despliegue sin stores, mantenimiento centralizado, responsive Tailwind, RBAC y auditoria directos; limitacion: depende de navegador/conectividad, acceso hardware limitado (no requerido).", bullet=True, size=10, after=2)
h("4.2 Hibrido DHARMA — i* (punto 1 del enunciado)",2)
p("DHARMA (Discovering Hybrid Architectures by Modelling Actors) estructura la arquitectura generica identificando actores/dominios atomicos y servicios antes de elegir componentes. Sistemas hibridos modernos integran COTS, FOSS, servicios Web, software a medida y legados de forma oportunista (entorno organizacional + mercado + integracion + recursos en paralelo). Dominio atomico: agrupacion funcional indivisible que aporta valor; un actor puede mapear a varios componentes y viceversa.", size=9)
p("i* SD (Dependencias Estrategicas): nivel intencional entre depender/dependee con Recursos, Tareas, Objetivos y softgoals. SR (Racional Estrategico): interior del actor con descomposicion medio-fin y tarea-descomposicion. Actores se especializan en Agentes, Roles y Posiciones. Fases DHARMA: modelado entorno organizacion (actores externos Personas/Organizaciones/Hardware/Software, dependencias objetivo/recurso/softgoal, evitando tarea prescriptiva), entorno del sistema (reorienta dependencias hacia el sistema + nuevos actores como Admins), descomposicion del sistema (SR con objetivo raiz -> subobjetivos jerarquicos = servicios), identificacion actores (agrupar en dominios atomicos via mercado/taxonomias, centrales vs soporte, patrones reusables, caso ETAPATELECOM S.A.).", size=9)
p("CCOLCAS es hibrido DHARMA: integra FOSS (Laravel 12, PHP 8.2, MySQL), servicios Web (Vite, Tailwind, FontAwesome), a medida (PedidoWorkflowService, AuditObserver, RoleMiddleware) y futuros COTS (pasarela/facturacion). Dominios atomicos centrales: Catalogo, Inventario multialmacen, Motor de Precios, Workflow Pedidos (historial_pedido), Ventas; soporte: RBAC, Auditoria (bitacora_cambios), Autenticacion. Esto justifica la estrategia incremental y la separacion OOHDM.", size=9)
p("Conclusion: tecnicamente web interactiva; arquitectonicamente hibrida DHARMA, coherente con OOHDM y el plan incremental.", bold=True, size=10, color="0F172A")

# 5
h("5. Esquema de la arquitectura utilizada (PA1 §6 / PA3)",1)
h("5.1 Tipo",2)
p("Aplicacion Web Interactiva: cliente dinamico (HTML, CSS separado por area, JS) y servidor dinamico (PHP 8.2 + Laravel 12) que procesa solicitudes, aplica logica (Controllers/Services/Observers) y persiste en MySQL (ccolcasdb) via Eloquent. XAMPP Apache/MySQL para dev/pruebas.", size=10)
h("5.2 Tecnologias (PA1 §6.2)",2)
add_table(["Capa","Tecnologia","Uso"],[["Presentacion","HTML5+Blade, CSS (admin/pedidos/inicio/carrito), Tailwind 4","Layouts Blade, responsive, CSS por area"],["Interactividad","JS (admin/inicio/carrito), Vite 7","Precio auto, confirm dialogs, badge"],["Servidor","PHP 8.2, Laravel 12 (MVC)","28 rutas, Controllers, Services, Observers, Middleware"],["Datos","MySQL (ccolcasdb), Eloquent, SQLite tests","19 tablas, 19 Factories, Seeders"],["Entorno","XAMPP, Composer, Node, Git, GitHub","https://github.com/75957473-design/Colcas.git"],["Seguridad","BCRYPT 12, @csrf, RoleMiddleware","Auth Usuario hashed"]])
h("5.3 MVC (PA3)",2)
p("Modelos (app/Models/* 19): Eloquent belongsTo/hasMany/hasOne; Producto::calcularPrecioVenta(), stockTotal(), enStockCritico().", bullet=True, size=10, after=2)
p("Controladores (app/Http/Controllers/*): Home, Carrito, Pedido, Auth/Login/Register, Admin/* (validan, transaccionan con lockForUpdate).", bullet=True, size=10, after=2)
p("Vistas (resources/views/*): Blade + Tailwind; welcome, carrito, cliente/checkout/pedidos/pedido-show, admin/*, layouts/admin.", bullet=True, size=10, after=2)
p("Observers/Services: AuditObserver -> bitacora_cambios (datos_previos/nuevos); PedidoWorkflowService (maquina estados por rol) + 11 Services + 7 Contracts.", bullet=True, size=10, after=2)
p("Middleware: RoleMiddleware alias 'role' en bootstrap/app.php; config/auth.php model Usuario (usuarios).", bullet=True, size=10, after=2)
cap("Diagrama arquitectura MVC / Capas", "PA3 image3-4.png o captura app/Models + routes/web.php")
h("5.4 Funcionamiento general",2)
p("Dominio: productos, categorias, precios, inventario, clientes, pedidos, ventas, usuarios/roles (PA1 §6.3). Flujo: Catalogo activo -> Carrito sesion -> Checkout (auth, revalida precio/stock) -> Pedido Pendiente + historial -> Mis Pedidos (stepper/timeline) -> Workflow por rol (Vendedor Confirma -> Almacenero En Almacen/Enviado -> Vendedor/Contador Entregado / Venta) -> Auditoria. XAMPP ejecuta Apache/MySQL.", size=10)
cap("Infraestructura XAMPP + .env", ".env DB_DATABASE=ccolcasdb + phpMyAdmin ccolcasdb")

# 6
h("6. Plan de trabajo — 4 semanas (PA1 §7, 04/09/2026)",1)
add_table(["Semana","Fechas","Actividades"],[["S1","04/09–10/09/2026","Analisis: procesos actuales, problemas, requerimientos, usuarios/roles/modulos."],["S2","11/09–17/09/2026","Diseno arquitectura, BD 19 tablas, interfaces, entorno Laravel+XAMPP."],["S3","18/09–24/09/2026","Desarrollo principales: usuarios/roles, productos/categorias/precios, inventario (mov+transferencia), clientes, catalogo, pedidos."],["S4","25/09–01/10/2026","Ventas, integracion, stock, pruebas, correcciones, UI, documentacion y presentacion."]])
p("S1 Analisis: levantar venta/almacen/precios/pedidos (WhatsApp, fisico, Excel). Definir 6 perfiles, 8 modulos A–H y alcance v1.", bullet=True, size=10, after=2)
p("S2 Diseno: ER, casos de uso/clases, mapa navegacion OOHDM, ADV Blade/Tailwind, crear proyecto Laravel (composer, .env, migrate, seed).", bullet=True, size=10, after=2)
p("S3 Desarrollo: CRUD productos (formula), categorias, stock_por_almacen+movimientos+transferencia, catalogo busqueda/categoria, clientes, pedidos historial + RBAC.", bullet=True, size=10, after=2)
p("S4 Integracion: ventas con descuento stock, workflow cliente/admin, tests (168), build Vite, correcciones, manuales y entregable (48h).", bullet=True, size=10, after=2)
cap("Evidencia plan / commits", "GitHub Commits https://github.com/75957473-design/Colcas.git + php artisan migrate:status")

# 7
h("7. Presentacion del analisis y diseno",1)
p("Analisis: problemas §2.3 -> necesidad §2.4 -> objetivos/modulos A–H -> alcance v1/v2 y beneficios. Requerimientos validados con entrevistas y restricciones reales (4 semanas, sin pasarela/facturacion, XAMPP local).", size=10)
p("Diseno: OOHDM conceptual (ER 19 tablas), navegacional (mapa §10), ADV (Blade layouts + CSS por area) e implementacion MVC. Calidad ISO 25010 y seguridad MADEJA/UCI/OWASP/INCIBE (§16–17). Evaluacion tripartita.", size=10)
add_table(["Artefacto","Contenido","Ubicacion"],[["Casos de Uso","6 actores x 8 modulos, include/extend","§8 + PA3 image1.png"],["Clases","19 entidades, atributos, metodos","§9 + PA3 image2.png"],["Navegacion","Nodos publico vs admin por rol","§10 + PA3 image3.png"],["Secuencias","Ventas, Abastecimiento, Transferencia","§11 + image5-7.png"],["ER","19 tablas + historial/bitacora","docs/03-base-de-datos.md"]])

# 8
h("8. Diagrama de Casos de Uso por modulo",1)
p("Diagrama general PA3 image1.png delimita aplicacion: Gerente, Subgerente, Contador, Almacenero, Vendedor y Cliente interactuan con 8 modulos. Detalle por modulo:", size=10)
add_table(["Modulo","Casos y actores"],[["A. Productos","Registrar/Modificar/Consultar/Desactivar, categorias (Gerente/Admin/etc). Cliente: consultar via catalogo."],["B. Inventario","Entradas/salidas, stock, ajuste, transferencia, historial (Almacenero/Admin)."],["C. Precios","Registrar/Actualizar (Contador/Admin)."],["D. Catalogo","Visualizar, caracteristicas, precios, buscar, filtrar, anadir (Cliente)."],["E. Clientes","Registrar/Administrar (Vendedor/Admin)."],["F. Pedidos","Registrar (Cliente web), Consultar, detalle, estado, entrega, seguimiento (workflow por rol)."],["G. Ventas","Registrar/consultar (Vendedor/Contador/Admin)."],["H. Usuarios/Roles","Administrar cuentas y 7 roles (Admin/Gerente)."]])
cap("Diagrama Casos de Uso — General", "PA3 image1.png — con fecha/hora")
for t in ["A. Productos","B. Inventario","C. Precios","D. Catalogo","E. Clientes","F. Pedidos","G. Ventas","H. Usuarios/Roles"]:
    cap(f"CU — {t}", f"CU {t} (si no hay por modulo, usar general + tabla)")

# 9
h("9. Diagrama de Clases",1)
p("19 entidades PA3 image2.png en app/Models/* con Eloquent. Relaciones: unidades_medida 1—* productos *—1 categorias; productos 1—* stock_por_almacen *—1 almacenes; roles 1—* usuarios 1—0..1 clientes 1—* pedidos 1—* detalle_pedidos *—1 productos; estados_pedido 1—* pedidos 1—0..1 ventas; pedidos 1—* historial_pedido; proveedores 1—* compras 1—* detalle_compras; tipos_movimiento 1—* movimientos_inventario; usuarios 1—* bitacora_cambios/historial.", size=9)
cap("Diagrama de Clases — Eloquent", "PA3 image2.png + php artisan generate:erd")
add_table(["Entidad","Atributos clave","Metodos"],[["Producto","codigo, nombre, descripcion, imagen, costo_compra, costo_flete, margen, precio_venta, stock_minimo","calcularPrecioVenta(), stockTotal(), enStockCritico()"],["Pedido","cliente_id, estado_id, vendedor_id, monto_total, direccion","historial(), venta(), detalles()"],["Usuario","nombre_completo, rol_id, email, password hashed, activo","esAdmin(), esGerente(), esCliente(), esPersonal(), etc."],["BitacoraCambio","usuario_id, tabla, accion, datos_previos/nuevos, fecha","AuditObserver"],["HistorialPedido","pedido_id, estado_ant/nuevo, usuario_id, observacion","—"]])

# 10
h("10. Mapa de Navegacion Web (OOHDM)",1)
add_table(["Zona","Nodos","Acceso"],[["Publico","/ (catalogo), /catalogo/buscar, /carrito","Todos"],["Cliente auth","/checkout, /mi-cuenta/pedidos, /mi-cuenta/pedidos/{id}","Cliente"],["Auth","POST /login, /logout, /cliente/registro","Todos"],["Admin","/admin/dashboard, /admin/productos, /admin/categorias","Personal (role)"],["Operaciones","/admin/inventario, /admin/pedidos, /admin/ventas","Personal"],["Gestion","/admin/clientes, /admin/usuarios (solo Admin/Gerente)","Restringido"]])
cap("Mapa de Navegacion Web", "PA3 image3.png — flechas Cliente vs Admin")

# 11
h("11. Gestion de procesos del negocio (Secuencias)",1)
p("Complementan vision estatica con comportamiento temporal (PA3).", size=10)
h("11.1 Flujo Comercial de Ventas y Atencion de Pedidos",3)
p("Cliente: Catalogo -> Carrito sesion -> Checkout (revalida precio/stock) -> Pedido Pendiente + historial -> Mis Pedidos stepper/timeline. Personal web: Pendiente->Confirmado (Vendedor) ->En Almacen->Enviado (Almacenero) ->Entregado (Contador/Vendedor) / Cancelado. Venta descuenta stock_por_almacen y crea movimientos. En PedidoController@store, PedidoWorkflowService y VentaController@store.", size=9)
cap("Secuencia — Flujo Comercial", "PA3 image5.png")
h("11.2 Abastecimiento: Compras e Ingesta",3)
p("Proveedor -> Compra (compras + detalle_compras + almacen) -> Movimiento ENTRADA -> StockPorAlmacen incrementa. Tablas proveedores/compras/detalle_compras/almacenes.", size=9)
cap("Secuencia — Abastecimiento", "PA3 image6.png")
h("11.3 Control: Transferencia e Inventario Multi-Almacen",3)
p("InventarioController@transferir: valida origen!=destino, lock origen, verifica stock, decrement origen / increment destino, 2 movimientos (Salida/Entrada por Transferencia), DB::transaction atomica.", size=9)
cap("Secuencia — Transferencia", "PA3 image7.png + /admin/inventario form Transferencia")

# 12
h("12. Interfaces de la aplicacion — Explicacion e ilustraciones",1)
p("Blade + Tailwind responsive; CSS separado por area (inicio, admin, pedidos, carrito); JS separado. Todas las capturas deben mostrar fecha/hora de tu PC!", bold=True, size=10, color="DC2626")
ifs=[("12.1 Catalogo (/)","Header busqueda + filtro categoria + badge; cards con imagen local, nombre, categoria, descripcion, precio S/, stock, boton Anadir. Solo activo. HomeController@index.","/  welcome.blade.php"),
("12.2 Carrito (/carrito)","Tabla editable (PUT cantidad, DELETE), subtotales, resumen. Sesion. CarritoController.","/carrito"),
("12.3 Registro / Login (modal)","Tabs Ingresar (email+password Hash::check) y Registrarse Cliente (nombre, doc, tel, email, pass). Crea Usuario+Cliente transaccional. Redirige por rol.","Modal en /"),
("12.4 Checkout (/checkout)","Direccion pre-llenada, comprobante Boleta/Factura, observaciones, resumen. Revalida vs BD. PedidoController@store.","/checkout (login+carrito)"),
("12.5 Mis Pedidos + Detalle","Lista filtrada por usuario, badge, stepper, tabla detalles; Ver detalle -> timeline historial_pedido + venta. pedidos.css.","/mi-cuenta/pedidos y /mi-cuenta/pedidos/{id}"),
("12.6 Admin Dashboard (/admin/dashboard)","Saludo, stats, sidebar (Principal/Catalogo/Operaciones/Personas), logout. layouts/admin.blade.php + admin.css.","/admin/dashboard"),
("12.7 Productos (/admin/productos)","Listar/buscar/filtrar, crear/editar _form (costo+flete+margen->precio auto admin.js), imagen upload, toggle.","/admin/productos"),
("12.8 Categorias + Inventario","Categorias CRUD + toggle. Inventario: stock por almacen, badge OK/Critico/Agotado, historial 20, movimiento, ajuste, transferencia.","/admin/categorias , /admin/inventario"),
("12.9 Pedidos Admin (/admin/pedidos)","Filtrar estado/q, detalle stepper+timeline+form Actualizar estado (solo transiciones por rol) + observacion + Registrar venta.","/admin/pedidos y /admin/pedidos/{pedido}"),
("12.10 Ventas (/admin/ventas)","Listar/buscar, crear desde pedido (tipo/numero comprobante, metodo pago, monto), show. Descuenta stock.","/admin/ventas"),
("12.11 Clientes (/admin/clientes)","Listar/ver/editar (DNI/RUC, telefono, direccion, persona recepcion).","/admin/clientes"),
("12.12 Usuarios y Roles (/admin/usuarios)","Solo Admin/Gerente: CRUD, toggle activo, 7 roles.","/admin/usuarios")]
for tit,desc,ruta in ifs:
    h(tit,2); p(desc, size=9)
    q=doc.add_paragraph(); rr=q.add_run("Ruta: "+ruta); rr.font.name='Arial'; rr.font.size=Pt(8); rr.italic=True
    q.paragraph_format.space_after=Pt(1)
    cap(tit, ruta)

# 13
h("13. Metodo, metodologia y estrategia (PA3)",1)
h("13.1 Metodologia: OOHDM",2)
p("Elegida por desacoplar dominio, navegacion y presentacion, ideal para web con catalogo publico + paneles privados por rol. Fases:", size=10)
add_table(["Fase OOHDM","Entregable CCOLCAS"],[["Diseno Conceptual","ER 19 tablas (dbcolcasdb): productos, categorias, almacenes, stock_por_almacen, pedidos, historial, ventas, compras, usuarios/roles, etc."],["Diseno Navegacional","Nodos Catalogo/Carrito/Checkout vs Admin por rol (RoleMiddleware); mapa §10."],["Interfaz Abstracta (ADV)","Blade + Tailwind (login, layouts/admin, pedido-show) + CSS por area."],["Implementacion","Laravel 12 MVC + Eloquent + Vite + XAMPP Apache/MySQL."]])
h("13.2 Estrategia: Modelo Incremental",2)
p("Reduce riesgo de integracion y permite ajustar reglas sobre componentes estables.", size=10)
add_table(["Incremento","Contenido"],[["1 Infraestructura","XAMPP (PHP 8.2, MySQL), repo GitHub, base Laravel 12."],["2 Persistencia/Seguridad","BD 19 tablas, .env, Usuario hashed, Auth + RoleMiddleware + BCRYPT 12."],["3 Catalogo/Almacen","CRUD productos/categorias/unidades, stock_por_almacen, precios, transferencias."],["4 Comercial/Auditoria","Pedidos workflow + historial, ventas comprobantes, bitacora Observer, pruebas integracion."]])
h("13.3 Metodo de evaluacion: Tripartita (PA3)",2)
p("Heuristica: 10 principios Nielsen sobre auth/catalogo/checkout (visibilidad estado, consistencia, manejo errores) + Pruebas funcionales (bloqueo sin stock, precios solo Contador/Admin, descuento venta/transferencia) + SUS (encuesta a 8-12 usuarios: clientes, vendedores, almaceneros, contadores).", size=10)

# 14
h("14. Modelo de calidad — ISO/IEC 25010 (PA3)",1)
p("4 pilares priorizados (SQuaRE):", size=10)
add_table(["Pilar","Cumplimiento CCOLCAS"],[["Adecuacion Funcional","ER sin redundancia, transacciones lockForUpdate, stepper/timeline fiel, 168 tests (335 asserts)."],["Seguridad","Bcrypt 12, @csrf, RoleMiddleware, validacion, auditoria bitacora/historial, .env."],["Usabilidad","Responsive Tailwind, CSS por area, stepper/timeline visibles, curva minima."],["Mantenibilidad","MVC nativo, Services/Contracts, factories/seeders, GitHub versionado."]])
p("Trazabilidad mantenibilidad/adecuacion -> bitacora (datos_previos/nuevos) y separacion OOHDM.", size=9, italic=True)

# 15
h("15. Pruebas de validacion (sustento)",1)
p("Que pruebas validarian que CCOLCAS cumple requerimientos y no es vulnerable? (PA3 + MADEJA/UCI/INCIBE)", size=10)
h("15.1 Unitarias (168 actuales)",3)
p("Autenticacion, Carrito, Catalogo, ConfirmacionPedido, Inventario, Pedido, ProductoModel (precio/stock critico BVA), RegistroCliente, SeleccionProducto, Venta, etc. Con mocks. Comando: php artisan test (sqlite :memory:). Resultado: 168 passed — capturar con fecha/hora.", size=9)
cap("Pruebas unitarias — php artisan test", "Terminal: php artisan test")
h("15.2 Funcionales de reglas (PA3 § Pruebas Funcionales)",3)
add_table(["Regla","Prueba","Esperado"],[["Stock","Anadir > stockTotal o Checkout sin stock","Rechaza ValidationException / redirect carrito"],["Precios","Actualizar precio como Vendedor","403 (solo Contador/Admin)"],["Venta","Registrar venta -> stock","Decrementa lockForUpdate + movimientos + historial"],["Checkout","Manipular precio sesion","Re-calcula vs BD"],["Roles","Cliente GET /admin/dashboard","403"]])
h("15.3 Feature / Integracion",3)
p("CatalogoFeature (6): solo activos, imagenes locales, busqueda/categoria; LoginFeature (7): form, credenciales, rol Cliente vs personal, logout; SeleccionarProductoFeature (8): seleccion, stock, subtotal.", size=9)
h("15.4 Heuristica Nielsen 10",3)
p("Visibilidad estado (stepper/badge), consistencia layouts, prevencion errores (validacion inline, confirm dialogs admin.js), control usuario (volver, filtros), estetica minimalista. Checklist MADEJA + INCIBE B/A.", size=9)
h("15.5 SUS (System Usability Scale)",3)
p("Encuesta a 8-12 usuarios representativos tras flujo Catalogo->Checkout->Seguimiento y rol especifico. Escala 0-100; meta >68.", size=9)
h("15.6 Propuestas adicionales",3)
p("Carga (ApacheBench/JMeter) sobre /catalogo/buscar y /checkout concurrente; seguridad (OWASP ZAP: inyeccion, XSS, CSRF); regresion por incremento. Ver docs/Planificacion de Pruebas.docx.", size=9)

# 16
h("16. Medidas de seguridad implementadas",1)
p("Pilares MADEJA (disponibilidad, autenticidad, integridad, confidencialidad, trazabilidad) + RNFS UCI 1-37 + INCIBE PYME.", size=10, italic=True)
add_table(["Pilar / UCI","Medida CCOLCAS","Evidencia"],[["Autenticidad RNFS 27-37","Hash::check, casts hashed, BCRYPT 12, sin defaults, @csrf","Usuario casts, LoginController, .env"],["Autorizacion","RoleMiddleware 'role', helpers esAdmin() etc., /admin 403 Cliente","bootstrap/app.php, web.php"],["Integridad RNFS 1-7","Validacion entrada/salida, anti-XSS Blade, anti-CSRF, paginate LIMIT","ValidateData, {{ }}, @csrf"],["Confidencialidad 8-14","Ocultar IDs, suppress mensajes, no datos sensibles, no cache sensible","403 generico, APP_DEBUG=false prod"],["Trazabilidad / No repudio 24-26","AuditObserver -> bitacora + historial_pedido","AuditObserver, AppServiceProvider"],["Disponibilidad","lockForUpdate, transacciones, modo fallo cierra","PedidoController, InventarioController"]])
h("16.1 Mapeo OWASP Top 10",3)
add_table(["Riesgo","Mitigado en CCOLCAS"],[["A01 Inyeccion","Eloquent parametrizado, sin raw SQL."],["A02 XSS","Blade {{ }} escapa, lista blanca."],["A03 Auth/Sesion","Session file, Bcrypt 12, logout invalida."],["A04 Ref directa","RBAC + exists:"],["A05 CSRF","@csrf en POST/PATCH/DELETE."],["A06 Config",".env, BCRYPT 12, APP_DEBUG false."],["A07 Cripto","Bcrypt, no texto plano."],["A08 Restriccion URL","Middleware role."],["A09 Comunicaciones","Preparado HTTPS (ver §17)."],["A10 Redireccion","route() internas."]])
h("16.2 Desarrollo por terceros / CMS (INCIBE)",3)
p("No usa CMS (evita modulos innecesarios, prefijo tablas, CAPTCHA). Usa FOSS auditado (Laravel). Para futuros COTS (pasarela/facturacion) exigir clausulas OWASP y privacidad por diseno.", size=9)

# 17
h("17. Contramedidas de seguridad propuestas",1)
p("Mas alla de lo implementado, priorizadas por impacto/esfuerzo (INCIBE B/A + MADEJA + UCI):", size=10)
add_table(["Contramedida","INCIBE","Como implementar en CCOLCAS"],[["HTTPS/TLS (Let's Encrypt)","Basico","APP_URL https, middleware Secure, HSTS, INTEGRAL."],["2FA Admin/Gerente","Avanzado","Fortify/Google2FA."],["Rate limiting + CAPTCHA login","Basico","Throttle RNFS 29, ReCAPTCHA."],["CSP + headers seguros","Basico","Middleware SetSecurityHeaders, RNFS 19."],["Backups + DMZ/firewall","Basico/Avanz","MySQL dump diario, VPS DMZ, IDS."],["Politica pass 12+ rot 90d retardo","Basico","Validador, Lockout, throttling RNFS 30-37."],["Cierre inactividad 15m + destroy sesion","Basico","SESSION_LIFETIME 15, AutoLogout RNFS 36-37."],["Logging + IDS + antivirus","Avanzado","storage/logs + IDS/IPS, antimalware."],["Sello confianza + auditoria + SLA","Avanzado","Auditoria OWASP, SLA hosting."]])
p("Inmediato antes de prod: TLS + rate limiting + headers + backups + SESSION 15. 2FA y auditoria en siguiente incremento.", size=9, italic=True, color="0F172A")

# 18
h("18. Conclusiones",1)
for t in ["CCOLCAS centraliza productos/precios/inventario/pedidos/ventas y catalogo, cumpliendo 8 modulos A–H y alcance v1 (§4.1) sin pagos/facturacion electronica (§4.2). Sustituye Excel/fisico y WhatsApp informal.",
"Es web interactiva (no nativa/hibrida movil) e hibrida DHARMA (FOSS + servicios + a medida), justificada por OOHDM e incremental: cada incremento entrega valor auditable.",
"Arquitectura MVC + RBAC + Auditoria (Observer + historial) + Workflow por rol demuestra separacion, trazabilidad ISO 25010 y seguridad MADEJA/UCI (hash, CSRF, RBAC, logs). Workflow 100% web responde a necesidad §2.4.",
"Pruebas (168 unit + 21 feature + funcionales + heuristica + SUS) validan reglas criticas y usabilidad; OWASP mitigado y contramedidas (TLS, 2FA, rate limiting) dejan listo para prod.",
"Cumple PA1 + PA3 + Entregable Final: nombre, descripcion, producto, arquitectura, plan 4 semanas, casos/clases/navegacion/secuencias, interfaces con capturas fechadas, metodologia/calidad, validacion, seguridad y anexos."]:
    p(t, bullet=True, size=10, after=3)

# 19
h("19. Referencias bibliograficas",1)
refs=["DHARMA — Discovering Hybrid Architectures by Modelling Actors (i* SD/SR, ETAPATELECOM S.A.).",
"MADEJA — Junta de Andalucia. Conceptos de Seguridad en Aplicaciones Web (pilares, BASIC/DIGEST/FORM/CLIENT-CERT, <security-constraint>).",
"UCI. Requisitos de Seguridad para Aplicaciones Web — RNFS 1-37 (CMMI, ISO 27001/27002/25010, OWASP).",
"SI frente a abuso, error y hurto — Vulnerabilidades (diseno/implementacion/uso), hacking, spoofing, sniffing, Firewalls/IDS/Antivirus.",
"INCIBE. Proteccion Pagina Web: Politicas PYME — Checklist Basico/Avanzado, HTTPS, RGPD/ARCO, CMS, logging, DoS, sellos/auditorias.",
"ISO/IEC 25010 SQuaRE — Calidad (adecuacion, seguridad, usabilidad, mantenibilidad).",
"OWASP Top 10 2021.",
"Laravel 12 Docs — MVC, Eloquent, Blade, Vite, Auth.",
"PA1_Suble_Lima (3).docx y PA3_Suble_Lima (2).docx — Base CCOLCAS."]
for i,r in enumerate(refs,1):
    q=doc.add_paragraph(); rr=q.add_run(f"[{i}] {r}"); rr.font.name='Arial'; rr.font.size=Pt(8); rr.font.color.rgb=RGBColor(0x47,0x56,0x6B); q.paragraph_format.space_after=Pt(2)

# 20 ANEXOS
h("20. Anexos",1)
p("Todos los anexos con fecha/hora visible en la captura (barra tareas o date).", bold=True, size=10, color="DC2626")
h("Anexo A — Codigo por modulo",2)
p("Fragmentos representativos (completo en GitHub).", size=9, italic=True)
code("A.1 Producto — calculo precio (Producto.php + ProductoController)","""// Producto.php
public function calcularPrecioVenta(): float {
  $costoTotal = $this->costo_compra + $this->costo_flete;
  return round($costoTotal * (1 + $this->margen_ganancia / 100), 2);
}
// ProductoController
if ((float)$data['precio_venta'] > 0) return round((float)$data['precio_venta'],2);
return round(((float)$data['costo_compra']+(float)$data['costo_flete'])*(1+(float)$data['margen_ganancia']/100),2);""")
code("A.2 Inventario — Transferencia (InventarioController@transferir)","""DB::transaction(function() use($data){
  $origen = StockPorAlmacen::where(...)->lockForUpdate()->first();
  if($data['cantidad'] > $origen->cantidad) throw new ...;
  $origen->decrement('cantidad', $data['cantidad']);
  $destino = StockPorAlmacen::firstOrCreate([...], ['cantidad'=>0]);
  $destino->increment('cantidad', $data['cantidad']);
  // 2 movimientos: Salida/Entrada por Transferencia
});""")
code("A.3 Pedidos — Workflow por rol (PedidoWorkflowService)","""// TRANSITIONS: Pendiente->[Confirmado,Cancelado], Confirmado->[En Almacen,Cancelado], ...
// ROLE_FOR_TARGET['Confirmado']=['Vendedor','Administrador','Gerente','Subgerente']
public static function puedeTransitar(Pedido $p, string $dest, string $rol): bool { ... }
PedidoWorkflowService::transitar($pedido, $dest->nombre, Auth::id());
HistorialPedido::create([... estado anterior/nuevo, usuario_id, observacion]);""")
code("A.4 Auditoria — Observer","""// AuditObserver created/updated/deleted -> BitacoraCambio::create(['datos_previos'=>json,'datos_nuevos'=>json])
// AppServiceProvider::boot(): Producto::observe(AuditObserver::class); Pedido::observe(...); Venta::observe(...);""")
code("A.5 RBAC — Middleware","""// bootstrap/app.php -> alias ['role'=>RoleMiddleware::class]
// web.php -> prefix('admin')->middleware('role:Administrador,Gerente,...')
// RoleMiddleware: in_array($rol->nombre, $roles) ? $next : abort(403);""")
code("A.6 Ventas — Descuento stock","""DB::transaction(function() use($data,$pedido){
  $venta = Venta::create([...]);
  foreach($pedido->detalles as $d){
    $stock = StockPorAlmacen::where('producto_id',$d->producto_id)->lockForUpdate()->first();
    $stock?->decrement('cantidad',$d->cantidad);
    MovimientoInventario::create([... Salida por Venta ..., 'usuario_id'=>Auth::id()]);
  }
});""")
code("A.7 Checkout — PedidoController@store","""DB::transaction(function() use($request,$cliente,$estadoPendiente,$carrito){
  $productos=Producto::with('stocks')->whereIn('id', array_keys($carrito))->get()->keyBy('id');
  // revalida precio_venta/stock vs BD, luego Pedido + DetallePedido + HistorialPedido
});""")
h("Anexo B — Codigo conexion BD",2)
code("B.1 .env","""APP_NAME=CCOLCAS
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ccolcasdb
DB_USERNAME=root
DB_PASSWORD=
BCRYPT_ROUNDS=12
SESSION_DRIVER=file""")
code("B.2 config/database.php + migracion","""// config/database.php 'mysql' => ['host'=>env('DB_HOST'),'database'=>env('DB_DATABASE','ccolcasdb')]
// 0001_..._create_users_table.php crea 19 tablas
// php artisan migrate --force && php artisan db:seed --force (7 roles + 6 estados + catalogo)""")
code("B.3 config/auth.php","""'providers'=>['users'=>['driver'=>'eloquent','model'=>App\\Models\\Usuario::class]] // tabla usuarios""")
cap("Anexo B — Captura phpMyAdmin", "phpMyAdmin con BD ccolcasdb y 19 tablas")
h("Anexo C — Capturas de pruebas por modulo (con fecha/hora)",2)
p("Nota oficial: 'Dichas capturas deben estar tomadas con la fecha y hora de su computador.' Hacer Win+Shift+S mostrando reloj o 'date && php artisan test'.", bold=True, size=9, color="DC2626")
add_table(["Anexo","Modulo / Prueba","Ruta / Comando"],[["C.1","Catalogo","/, /catalogo/buscar + php artisan test --filter=CatalogoFeature"],["C.2","Carrito","/carrito + POST /carrito/agregar"],["C.3","Auth","Modal login en /"],["C.4","Checkout->Pedido","/checkout -> /mi-cuenta/pedidos"],["C.5","Inventario","/admin/inventario (mov/ajuste/transferencia)"],["C.6","Productos/Precios","/admin/productos"],["C.7","Pedidos workflow","/admin/pedidos/{id}"],["C.8","Ventas","/admin/ventas"],["C.9","RBAC","/admin/dashboard por rol (Cliente 403)"],["C.10","Tests globales","php artisan test (168) + npm run build"]])
for cc,_,cmd in [("C.1","Catalogo","/"),("C.2","Carrito","/carrito"),("C.3","Auth","/ login"),("C.4","Checkout","/checkout"),("C.5","Inventario","/admin/inventario"),("C.6","Productos","/admin/productos"),("C.7","Pedidos","/admin/pedidos"),("C.8","Ventas","/admin/ventas"),("C.9","RBAC","/admin/dashboard"),("C.10","Tests","php artisan test")]:
    cap(f"{cc} — {cmd}", cmd)

pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER; pp.paragraph_format.space_before=Pt(16)
r=pp.add_run("— Fin del Entregable Final —  CCOLCAS  •  Suble Lima Denilson Rivaldo  •  Ingenieria Web  •  Sede Cusco 2026 —"); r.bold=True; r.font.name='Arial'; r.font.size=Pt(9); r.font.color.rgb=RGBColor(0x0F,0x17,0x2A)
pp=doc.add_paragraph(); pp.alignment=WD_ALIGN_PARAGRAPH.CENTER
r=pp.add_run("Reemplaza todos los bloques [CAPTURA] con imagenes reales antes de subir. Guardar como: Suble_Lima_Denilson_Rivaldo_EntregableFinal.docx  •  Arial 12"); r.font.name='Arial'; r.font.size=Pt(8); r.italic=True; r.font.color.rgb=RGBColor(0xDC,0x26,0x26)

out = r"docs/IngWebEntregabes/EntregableFinal_Suble_Lima_Denilson_Rivaldo.docx"
doc.save(out)
print(f"Guardado en {out}")

