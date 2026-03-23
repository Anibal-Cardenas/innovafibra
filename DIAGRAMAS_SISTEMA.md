<!-- DIAGRAMA DE FLUJO DEL SISTEMA DE COMISIONES -->
<!-- Abrir este archivo en un visualizador Mermaid o en VS Code con extensión Mermaid -->

# Diagrama de Flujo - Sistema de Comisiones

## Flujo Principal

```mermaid
graph TD
    A[Operador Inicia Sesión] -->|Ver menú limitado| B[Registrar Producción]
    B --> C{¿Producción Registrada?}
    C -->|Sí| D[Estado: PENDIENTE]
    C -->|No| B
    
    D --> E[Admin/Supervisor Valida]
    E --> F{¿Decisión?}
    F -->|APROBAR| G[Estado: APROBADO]
    F -->|RECHAZAR| H[Estado: RECHAZADO]
    
    G --> I[Operador ve comisión estimada]
    H --> I
    
    I --> J[Admin calcula comisión periodo]
    J --> K[Sistema crea comisión]
    K --> L[Estado: CALCULADO]
    
    L --> M[Admin registra pago]
    M --> N[Estado: PAGADO]
    
    N --> O[Operador ve comisión en historial]
    O --> P[Puede ver detalle completo]
```

## Roles y Permisos

```mermaid
graph LR
    A[Usuarios del Sistema] --> B[Administrador]
    A --> C[Operador]
    A --> D[Vendedor]
    
    B --> B1[Acceso Total]
    B1 --> B2[Compras]
    B1 --> B3[Producción]
    B1 --> B4[Ventas]
    B1 --> B5[Inventario]
    B1 --> B6[Comisiones]
    B1 --> B7[Reportes]
    B1 --> B8[Configuración]
    
    C --> C1[Acceso Limitado]
    C1 --> C2[Registrar Producción]
    C1 --> C3[Ver Mis Producciones]
    C1 --> C4[Ver Mis Comisiones]
    
    D --> D1[Acceso Limitado]
    D1 --> D2[Registrar Ventas]
    D1 --> D3[Ver Ventas]
    D1 --> D4[Gestionar Entregas]
```

## Estados de una Comisión

```mermaid
stateDiagram-v2
    [*] --> Pendiente: Admin crea periodo
    Pendiente --> Calculado: Sistema calcula monto
    Calculado --> Pagado: Admin registra pago
    Calculado --> Anulado: Admin anula
    Pagado --> [*]
    Anulado --> [*]
    
    note right of Calculado
        Estado principal
        Listo para pagar
    end note
    
    note right of Pagado
        Comisión liquidada
        Operador puede ver detalle
    end note
```

## Flujo de Cálculo de Comisión

```mermaid
sequenceDiagram
    participant A as Administrador
    participant S as Sistema
    participant BD as Base de Datos
    participant O as Operador
    
    A->>S: Selecciona operador y periodo
    S->>BD: Consulta producciones aprobadas
    BD-->>S: Retorna lista de producciones
    S->>S: Calcula: bolsas × tarifa
    S->>BD: Crea registro en tabla comisiones
    S->>BD: Crea detalles en comisiones_detalle
    S->>BD: Marca producciones como liquidadas
    S-->>A: Muestra comisión calculada
    
    Note over O: Operador puede consultar
    O->>S: Consulta "Mis Comisiones"
    S->>BD: Obtiene comisiones del operador
    BD-->>S: Retorna comisiones
    S-->>O: Muestra lista y detalles
```

## Arquitectura del Sistema

```mermaid
graph TB
    subgraph "Capa de Presentación"
        V1[Vista: mis_comisiones.php]
        V2[Vista: admin.php]
        V3[Vista: detalle.php]
        H[Header dinámico por rol]
    end
    
    subgraph "Capa de Negocio"
        CC[ComisionesController]
        PC[ProduccionController]
        VC[VentasController]
        AC[AuthController]
    end
    
    subgraph "Capa de Datos"
        DB[(Base de Datos)]
        T1[Tabla: comisiones]
        T2[Tabla: comisiones_detalle]
        T3[Tabla: producciones]
        T4[Tabla: usuarios]
        SP1[SP: calcular_comision]
        SP2[SP: registrar_pago]
        VW1[Vista: v_comisiones_pendientes]
    end
    
    V1 --> CC
    V2 --> CC
    V3 --> CC
    H --> AC
    
    CC --> DB
    PC --> DB
    VC --> DB
    
    DB --> T1
    DB --> T2
    DB --> T3
    DB --> T4
    DB --> SP1
    DB --> SP2
    DB --> VW1
```

## Relaciones de Tablas (ERD Simplificado)

```mermaid
erDiagram
    USUARIOS ||--o{ PRODUCCIONES : "registra"
    USUARIOS ||--o{ COMISIONES : "tiene"
    COMISIONES ||--|{ COMISIONES_DETALLE : "contiene"
    PRODUCCIONES ||--o| COMISIONES_DETALLE : "incluida en"
    PRODUCCIONES }o--|| COMISIONES : "pertenece a"
    
    USUARIOS {
        int id_usuario PK
        string username
        string rol
        decimal tarifa_por_bolsa
        string estado
    }
    
    PRODUCCIONES {
        int id_produccion PK
        int id_operario FK
        date fecha_produccion
        int cantidad_producida
        string estado_validacion
        int id_comision FK
    }
    
    COMISIONES {
        int id_comision PK
        int id_operario FK
        date fecha_inicio
        date fecha_fin
        int total_bolsas_producidas
        decimal monto_total
        string estado
    }
    
    COMISIONES_DETALLE {
        int id_comision_detalle PK
        int id_comision FK
        int id_produccion FK
        int cantidad_bolsas
        decimal subtotal
    }
```

## Flujo de Permisos por Controlador

```mermaid
flowchart TD
    REQ[Request HTTP] --> AUTH{¿Usuario Autenticado?}
    AUTH -->|No| LOGIN[Redirigir a Login]
    AUTH -->|Sí| ROLE{¿Qué rol tiene?}
    
    ROLE -->|Administrador| ADMIN[Acceso Completo]
    ROLE -->|Operador| OPE[Acceso Limitado]
    ROLE -->|Vendedor| VEND[Acceso Limitado]
    
    ADMIN --> A1[Compras ✓]
    ADMIN --> A2[Producción ✓]
    ADMIN --> A3[Ventas ✓]
    ADMIN --> A4[Comisiones ✓]
    ADMIN --> A5[Reportes ✓]
    
    OPE --> O1[Producción ✓]
    OPE --> O2[Mis Comisiones ✓]
    OPE --> O3[Compras ✗]
    OPE --> O4[Ventas ✗]
    
    VEND --> V1[Ventas ✓]
    VEND --> V2[Entregas ✓]
    VEND --> V3[Compras ✗]
    VEND --> V4[Comisiones ✗]
```

---

## Leyenda

- **→** : Flujo normal
- **✓** : Permitido
- **✗** : Denegado
- **PK** : Primary Key
- **FK** : Foreign Key
- **SP** : Stored Procedure

---

## Notas de Implementación

1. Todos los diagramas están en formato Mermaid
2. Pueden visualizarse en VS Code con la extensión "Markdown Preview Mermaid Support"
3. O en cualquier visualizador online de Mermaid (mermaid.live)
4. Los diagramas reflejan la arquitectura implementada

---

**Fecha:** 14 de Enero, 2026  
**Versión:** 1.0
