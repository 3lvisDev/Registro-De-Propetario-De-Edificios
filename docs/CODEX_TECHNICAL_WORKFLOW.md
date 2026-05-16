# Flujo de Trabajo Técnico para Codex

## Objetivo

Estandarizar cómo Codex debe trabajar en todos los proyectos:

- Debugging sistemático.
- Refactorización hacia arquitectura modular.
- Pruebas automatizadas como contrato.
- Documentación técnica.
- Despliegue seguro con rollback.

Regla principal:

```txt
Codex = ingeniero asistente
Humano = arquitecto y revisor final
Tests = contrato de seguridad
CI/CD = juez objetivo
```

## Principios operativos

1. Diagnóstico antes de cambios.
2. Debugging primero, refactor después.
3. Fix mínimo seguro antes de optimizar.
4. No mezclar bugfix + refactor + feature sin solicitud explícita.
5. Mantener compatibilidad externa.

## Flujo técnico obligatorio

### Fase 1 - Diagnóstico

- Entender arquitectura actual.
- Identificar módulos, dependencias y riesgos.
- Definir archivos candidatos y cobertura de pruebas requerida.
- No modificar código en esta fase.

### Fase 2 - Debugging sistemático

1. Reproducir el bug.
2. Aislar el flujo afectado.
3. Proponer 2-3 hipótesis.
4. Verificar hipótesis con evidencia.
5. Confirmar causa raíz.
6. Aplicar parche mínimo.
7. Validar con pruebas.
8. Documentar causa/fix/riesgo.

### Fase 3 - Refactorización controlada

- Separar controller / use case / domain / infrastructure.
- Extraer funciones puras y responsabilidades únicas.
- Reducir acoplamiento.
- Mantener comportamiento externo.
- Acompañar con pruebas.

### Fase 4 - Quality gates previos a PR

Ejecutar cuando aplique:

- npm ci
- npm run lint
- npm run typecheck
- npm test
- npm run test:integration
- npm run build

No declarar éxito si no hay evidencia real de ejecución.

## Estrategia para errores complejos

Analizar por capas:

1. Interfaces/Controllers
2. Application/Use-cases
3. Domain/Entities/Services
4. Infrastructure/Database/Providers
5. Deployment/Runtime

Para cada capa documentar: causa posible, evidencia, prueba y riesgo.

## Gate de despliegue

No desplegar si falla cualquiera de:

- lint
- typecheck
- tests
- build
- health checks
- migraciones/compatibilidad

## Resumen mínimo requerido en cada PR técnico

- Problema
- Causa raíz
- Solución
- Archivos modificados
- Tests/checks ejecutados
- Riesgos
- Plan de rollback
