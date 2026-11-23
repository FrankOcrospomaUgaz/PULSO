--
-- PostgreSQL database dump
--

-- Dumped from database version 16.2
-- Dumped by pg_dump version 16.2

-- Started on 2025-02-19 21:49:01

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 2 (class 3079 OID 192092)
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- TOC entry 5962 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- TOC entry 387 (class 1255 OID 159301)
-- Name: trigger_insert_kardex(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trigger_insert_kardex() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_producto_clasificacion TEXT;
    v_stock_anterior INTEGER;
    v_stock_actual INTEGER;
    v_precio_unitario NUMERIC;
    v_total NUMERIC;
    v_movimiento_id INTEGER;
    v_usuario_id INTEGER;
BEGIN
    -- CASO INSERT: Cuando se inserta un nuevo registro en det_movventa
    IF (TG_OP = 'INSERT') THEN
        -- Verificar la clasificación del producto
        SELECT clasificacion INTO v_producto_clasificacion
        FROM producto
        WHERE id = NEW.producto_id;

        -- Si el producto no es de tipo "Bien", no hacer nada
        IF v_producto_clasificacion <> 'Bien' THEN
            RETURN NEW;
        END IF;

        -- Obtener el movimiento_id desde la tabla movventa
        SELECT movimiento_id INTO v_movimiento_id
        FROM movventa
        WHERE id = NEW.movventa_id;

        -- Si no se encuentra el movimiento_id, detener
        IF v_movimiento_id IS NULL THEN
            RAISE EXCEPTION 'No se encontró movimiento_id para el movventa_id %', NEW.movventa_id;
        END IF;

        -- Obtener el usuario_id desde la tabla movimiento
        SELECT usuario_id INTO v_usuario_id
        FROM movimiento
        WHERE id = v_movimiento_id;

        -- Si no se encuentra el usuario_id, detener
        IF v_usuario_id IS NULL THEN
            RAISE EXCEPTION 'No se encontró usuario_id para el movimiento_id %', v_movimiento_id;
        END IF;

        -- Obtener el stock actual del producto
        SELECT stockbase INTO v_stock_anterior
        FROM stockproducto
        WHERE producto_id = NEW.producto_id
          AND sucursal_id = NEW.sucursal_id
        FOR UPDATE;

        -- Si no hay stock, inicializarlo
        IF v_stock_anterior IS NULL THEN
            v_stock_anterior := 0;
        END IF;

        -- Calcular el nuevo stock
        v_stock_actual := v_stock_anterior - NEW.cantidad;

        -- Actualizar el stock del producto
        UPDATE stockproducto
        SET stockbase = v_stock_actual
        WHERE producto_id = NEW.producto_id
          AND sucursal_id = NEW.sucursal_id;

        -- Calcular valores necesarios para el Kardex
        v_precio_unitario := (NEW.monto / NEW.cantidad);
        v_total := NEW.monto;

        -- Insertar un registro en Kardex
        INSERT INTO kardex (
            detalle_id, producto_id, json_producto, unidad_id, json_listaunidad, cantidad,
            unidadbase_id, cantidadbase, preciounitario, moneda, tipocambio, total, fecha,
            situacion, usuario_id, usuario, movimiento_id, tipomovimiento_id, tipodocumento_id,
            sucursal_id, stockanterior, stockactual, created_at, updated_at
        ) VALUES (
            NEW.id, NEW.producto_id, NEW.json_producto, NEW.unidad_id, '{}', NEW.cantidad,
            NEW.unidad_id, NEW.cantidad, v_precio_unitario, 'PEN', 3.71, v_total, NOW(),
            'E', v_usuario_id, 'Sistema', v_movimiento_id, 2, 1, NEW.sucursal_id,
            v_stock_anterior, v_stock_actual, NOW(), NOW()
        );

        RETURN NEW;

    -- CASO DELETE: Cuando se elimina un registro en det_movventa
    ELSIF (TG_OP = 'DELETE') THEN
        -- Obtener el stock actual del producto
        SELECT stockbase INTO v_stock_anterior
        FROM stockproducto
        WHERE producto_id = OLD.producto_id
          AND sucursal_id = OLD.sucursal_id
        FOR UPDATE;

        -- Si no hay stock, inicializarlo
        IF v_stock_anterior IS NULL THEN
            v_stock_anterior := 0;
        END IF;

        -- Calcular el nuevo stock (reponiendo el stock eliminado)
        v_stock_actual := v_stock_anterior + OLD.cantidad;

        -- Actualizar el stock del producto
        UPDATE stockproducto
        SET stockbase = v_stock_actual
        WHERE producto_id = OLD.producto_id
          AND sucursal_id = OLD.sucursal_id;

        -- Eliminar el registro correspondiente en Kardex
        DELETE FROM kardex
        WHERE detalle_id = OLD.id;

        RETURN OLD;
    END IF;

    RETURN NULL;
END;
$$;


ALTER FUNCTION public.trigger_insert_kardex() OWNER TO postgres;

--
-- TOC entry 404 (class 1255 OID 159302)
-- Name: trigger_insert_update_kardex(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trigger_insert_update_kardex() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_producto_clasificacion TEXT;
    v_stock_anterior INTEGER;
    v_stock_actual INTEGER;
    v_movimiento_id INTEGER;
    v_usuario_id INTEGER;
BEGIN
    -- Verificar si es un DELETE
    IF (TG_OP = 'DELETE') THEN
        -- Verificar la clasificación del producto
        SELECT clasificacion INTO v_producto_clasificacion
        FROM producto
        WHERE id = OLD.producto_id;

        -- Si el producto no es de tipo "Bien", no hacer nada
        IF v_producto_clasificacion <> 'Bien' THEN
            RETURN OLD;
        END IF;

        -- Obtener el stock actual del producto
        SELECT stockbase INTO v_stock_anterior
        FROM stockproducto
        WHERE producto_id = OLD.producto_id
          AND sucursal_id = OLD.sucursal_id
        FOR UPDATE;

        -- Si no hay stock, inicializarlo
        IF v_stock_anterior IS NULL THEN
            v_stock_anterior := 0;
        END IF;

        -- Calcular el nuevo stock sumando la cantidad eliminada
        v_stock_actual := v_stock_anterior + OLD.cantidad;

        -- Actualizar el stock del producto
        UPDATE stockproducto
        SET stockbase = v_stock_actual
        WHERE producto_id = OLD.producto_id
          AND sucursal_id = OLD.sucursal_id;

        RETURN OLD;
    END IF;

    -- Verificar si es un INSERT
    IF (TG_OP = 'INSERT') THEN
        -- Verificar la clasificación del producto
        SELECT clasificacion INTO v_producto_clasificacion
        FROM producto
        WHERE id = NEW.producto_id;

        -- Si el producto no es de tipo "Bien", no hacer nada
        IF v_producto_clasificacion <> 'Bien' THEN
            RETURN NEW;
        END IF;

        -- Obtener el movimiento_id desde la tabla movventa
        SELECT movimiento_id INTO v_movimiento_id
        FROM movventa
        WHERE id = NEW.movventa_id;

        -- Si no se encuentra el movimiento_id, detener
        IF v_movimiento_id IS NULL THEN
            RAISE EXCEPTION 'No se encontró movimiento_id para el movventa_id %', NEW.movventa_id;
        END IF;

        -- Obtener el usuario_id desde la tabla movimiento
        SELECT usuario_id INTO v_usuario_id
        FROM movimiento
        WHERE id = v_movimiento_id;

        -- Si no se encuentra el usuario_id, detener
        IF v_usuario_id IS NULL THEN
            RAISE EXCEPTION 'No se encontró usuario_id para el movimiento_id %', v_movimiento_id;
        END IF;

        -- Obtener el stock actual del producto
        SELECT stockbase INTO v_stock_anterior
        FROM stockproducto
        WHERE producto_id = NEW.producto_id
          AND sucursal_id = NEW.sucursal_id
        FOR UPDATE;

        -- Si no hay stock, inicializarlo
        IF v_stock_anterior IS NULL THEN
            v_stock_anterior := 0;
        END IF;

        -- Calcular el nuevo stock
        v_stock_actual := v_stock_anterior - NEW.cantidad;

        -- Actualizar el stock del producto
        UPDATE stockproducto
        SET stockbase = v_stock_actual
        WHERE producto_id = NEW.producto_id
          AND sucursal_id = NEW.sucursal_id;

        -- Insertar un registro en Kardex
        INSERT INTO kardex (
            detalle_id, producto_id, json_producto, unidad_id, json_listaunidad, cantidad,
            unidadbase_id, cantidadbase, preciounitario, moneda, tipocambio, total, fecha,
            situacion, usuario_id, usuario, movimiento_id, tipomovimiento_id, tipodocumento_id,
            sucursal_id, stockanterior, stockactual, created_at, updated_at
        ) VALUES (
            NEW.id, NEW.producto_id, NEW.json_producto, NEW.unidad_id, '{}', NEW.cantidad,
            NEW.unidad_id, NEW.cantidad, (NEW.monto / NEW.cantidad), 'PEN', 3.71, NEW.monto, NOW(),
            'E', v_usuario_id, 'Sistema', v_movimiento_id, 2, 1, NEW.sucursal_id,
            v_stock_anterior, v_stock_actual, NOW(), NOW()
        );

        RETURN NEW;
    END IF;

    RETURN NULL; -- Por seguridad
END;
$$;


ALTER FUNCTION public.trigger_insert_update_kardex() OWNER TO postgres;

--
-- TOC entry 405 (class 1255 OID 159303)
-- Name: trigger_manage_kardex(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trigger_manage_kardex() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_producto_clasificacion TEXT;
    v_stock_anterior INTEGER;
    v_stock_actual INTEGER;
    v_movimiento_id INTEGER;
    v_usuario_id INTEGER;
    v_precio_unitario NUMERIC;
    v_total NUMERIC;
BEGIN
    -- Determinar si la acción es un INSERT o una actualización lógica
    IF TG_OP = 'INSERT' THEN
        -- Verificar la clasificación del producto
        SELECT clasificacion INTO v_producto_clasificacion
        FROM producto
        WHERE id = NEW.producto_id;

        -- Si el producto no es de tipo "Bien", no hacer nada
        IF v_producto_clasificacion <> 'Bien' THEN
            RETURN NEW;
        END IF;

        -- Obtener el movimiento_id desde la tabla movventa
        SELECT movimiento_id INTO v_movimiento_id
        FROM movventa
        WHERE id = NEW.movventa_id;

        -- Si no se encuentra el movimiento_id, detener
        IF v_movimiento_id IS NULL THEN
            RAISE EXCEPTION 'No se encontró movimiento_id para el movventa_id %', NEW.movventa_id;
        END IF;

        -- Obtener el usuario_id desde la tabla movimiento
        SELECT usuario_id INTO v_usuario_id
        FROM movimiento
        WHERE id = v_movimiento_id;

        -- Si no se encuentra el usuario_id, detener
        IF v_usuario_id IS NULL THEN
            RAISE EXCEPTION 'No se encontró usuario_id para el movimiento_id %', v_movimiento_id;
        END IF;

        -- Obtener el stock actual del producto
        SELECT stockbase INTO v_stock_anterior
        FROM stockproducto
        WHERE producto_id = NEW.producto_id
          AND sucursal_id = NEW.sucursal_id
        FOR UPDATE;

        -- Si no hay stock, inicializarlo
        IF v_stock_anterior IS NULL THEN
            v_stock_anterior := 0;
        END IF;

        -- Calcular el nuevo stock
        v_stock_actual := v_stock_anterior - NEW.cantidad;

        -- Actualizar el stock del producto
        UPDATE stockproducto
        SET stockbase = v_stock_actual
        WHERE producto_id = NEW.producto_id
          AND sucursal_id = NEW.sucursal_id;

        -- Calcular valores necesarios para el Kardex
        v_precio_unitario := (NEW.monto / NEW.cantidad);
        v_total := NEW.monto;

        -- Insertar un registro en Kardex
        INSERT INTO kardex (
            detalle_id, producto_id, json_producto, unidad_id, json_listaunidad, cantidad,
            unidadbase_id, cantidadbase, preciounitario, moneda, tipocambio, total, fecha,
            situacion, usuario_id, usuario, movimiento_id, tipomovimiento_id, tipodocumento_id,
            sucursal_id, stockanterior, stockactual, created_at, updated_at
        ) VALUES (
            NEW.id, NEW.producto_id, NEW.json_producto, NEW.unidad_id, '{}', NEW.cantidad,
            NEW.unidad_id, NEW.cantidad, v_precio_unitario, 'PEN', 3.71, v_total, NOW(),
            'E', v_usuario_id, 'Sistema', v_movimiento_id, 2, 1, NEW.sucursal_id,
            v_stock_anterior, v_stock_actual, NOW(), NOW()
        );

        RETURN NEW;

    ELSIF TG_OP = 'UPDATE' THEN
        -- Verificar si se está eliminando lógicamente (deleted_at no era nulo antes)
        IF OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL THEN
            -- Verificar la clasificación del producto
            SELECT clasificacion INTO v_producto_clasificacion
            FROM producto
            WHERE id = OLD.producto_id;

            -- Si el producto no es de tipo "Bien", no hacer nada
            IF v_producto_clasificacion <> 'Bien' THEN
                RETURN NEW;
            END IF;

            -- Obtener el stock actual del producto
            SELECT stockbase INTO v_stock_anterior
            FROM stockproducto
            WHERE producto_id = OLD.producto_id
              AND sucursal_id = OLD.sucursal_id
            FOR UPDATE;

            -- Si no hay stock, inicializarlo
            IF v_stock_anterior IS NULL THEN
                v_stock_anterior := 0;
            END IF;

            -- Calcular el nuevo stock al regresar la cantidad eliminada
            v_stock_actual := v_stock_anterior + OLD.cantidad;

            -- Actualizar el stock del producto
            UPDATE stockproducto
            SET stockbase = v_stock_actual
            WHERE producto_id = OLD.producto_id
              AND sucursal_id = OLD.sucursal_id;

            -- Eliminar el registro correspondiente en Kardex
            DELETE FROM kardex
            WHERE detalle_id = OLD.id;

            RETURN NEW;
        END IF;
    END IF;

    RETURN NULL; -- Por seguridad, nunca debería llegar aquí
END;
$$;


ALTER FUNCTION public.trigger_manage_kardex() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 216 (class 1259 OID 159304)
-- Name: banco; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.banco (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    orden integer NOT NULL,
    situacion boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.banco OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 159308)
-- Name: banco_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.banco_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.banco_id_seq OWNER TO postgres;

--
-- TOC entry 5963 (class 0 OID 0)
-- Dependencies: 217
-- Name: banco_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.banco_id_seq OWNED BY public.banco.id;


--
-- TOC entry 218 (class 1259 OID 159309)
-- Name: billeteradigital; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.billeteradigital (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    orden integer NOT NULL,
    situacion boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.billeteradigital OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 159313)
-- Name: billeteradigital_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.billeteradigital_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.billeteradigital_id_seq OWNER TO postgres;

--
-- TOC entry 5964 (class 0 OID 0)
-- Dependencies: 219
-- Name: billeteradigital_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.billeteradigital_id_seq OWNED BY public.billeteradigital.id;


--
-- TOC entry 220 (class 1259 OID 159314)
-- Name: caja; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.caja (
    id bigint NOT NULL,
    numero character varying(50) NOT NULL,
    situacion character varying(1) DEFAULT 'C'::character varying NOT NULL,
    serie character varying(50) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    sucursal_id bigint DEFAULT '39'::bigint NOT NULL
);


ALTER TABLE public.caja OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 159319)
-- Name: caja_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.caja_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.caja_id_seq OWNER TO postgres;

--
-- TOC entry 5965 (class 0 OID 0)
-- Dependencies: 221
-- Name: caja_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.caja_id_seq OWNED BY public.caja.id;


--
-- TOC entry 222 (class 1259 OID 159320)
-- Name: campos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.campos (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    comentario character varying(255) NOT NULL,
    longitud integer DEFAULT 0 NOT NULL,
    diccionario character varying(255) NOT NULL,
    validacion character varying(1) DEFAULT 'N'::character varying NOT NULL,
    msgvalidacion character varying(255) NOT NULL,
    longitudreporte integer DEFAULT 0 NOT NULL,
    alineacionreporte character varying(1) DEFAULT 'C'::character varying NOT NULL,
    tabla_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.campos OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 159329)
-- Name: campos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.campos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.campos_id_seq OWNER TO postgres;

--
-- TOC entry 5966 (class 0 OID 0)
-- Dependencies: 223
-- Name: campos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.campos_id_seq OWNED BY public.campos.id;


--
-- TOC entry 224 (class 1259 OID 159330)
-- Name: categoria; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categoria (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    abreviatura character varying(255) NOT NULL,
    nivel integer DEFAULT 1 NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    padre character varying(1) DEFAULT 'N'::character varying NOT NULL,
    imagen text NOT NULL,
    categoria_id bigint,
    empresa_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.categoria OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 159338)
-- Name: categoria_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categoria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categoria_id_seq OWNER TO postgres;

--
-- TOC entry 5967 (class 0 OID 0)
-- Dependencies: 225
-- Name: categoria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categoria_id_seq OWNED BY public.categoria.id;


--
-- TOC entry 226 (class 1259 OID 159339)
-- Name: categoriaparametros; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categoriaparametros (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.categoriaparametros OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 159343)
-- Name: categoriaparametros_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categoriaparametros_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categoriaparametros_id_seq OWNER TO postgres;

--
-- TOC entry 5968 (class 0 OID 0)
-- Dependencies: 227
-- Name: categoriaparametros_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categoriaparametros_id_seq OWNED BY public.categoriaparametros.id;


--
-- TOC entry 367 (class 1259 OID 192075)
-- Name: categoriapregunta; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categoriapregunta (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    situacion character(1) DEFAULT 'E'::bpchar NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.categoriapregunta OWNER TO postgres;

--
-- TOC entry 366 (class 1259 OID 192074)
-- Name: categoriapregunta_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categoriapregunta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categoriapregunta_id_seq OWNER TO postgres;

--
-- TOC entry 5969 (class 0 OID 0)
-- Dependencies: 366
-- Name: categoriapregunta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categoriapregunta_id_seq OWNED BY public.categoriapregunta.id;


--
-- TOC entry 228 (class 1259 OID 159344)
-- Name: conceptopago; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.conceptopago (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    tipo character(1) NOT NULL,
    restringido boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.conceptopago OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 159348)
-- Name: conceptopago_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.conceptopago_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.conceptopago_id_seq OWNER TO postgres;

--
-- TOC entry 5970 (class 0 OID 0)
-- Dependencies: 229
-- Name: conceptopago_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.conceptopago_id_seq OWNED BY public.conceptopago.id;


--
-- TOC entry 373 (class 1259 OID 192132)
-- Name: det_encuesta; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.det_encuesta (
    id bigint NOT NULL,
    enunciado text NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    obligatorio character(1) DEFAULT 'N'::bpchar NOT NULL,
    opciones json NOT NULL,
    situacion character(1) DEFAULT 'E'::bpchar NOT NULL,
    categoriapregunta_id bigint NOT NULL,
    categoriapregunta character varying(255) NOT NULL,
    tipopregunta_id bigint NOT NULL,
    encuesta_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.det_encuesta OWNER TO postgres;

--
-- TOC entry 5971 (class 0 OID 0)
-- Dependencies: 373
-- Name: COLUMN det_encuesta.obligatorio; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.det_encuesta.obligatorio IS 'S => Sí, N => No';


--
-- TOC entry 372 (class 1259 OID 192131)
-- Name: det_encuesta_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.det_encuesta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.det_encuesta_id_seq OWNER TO postgres;

--
-- TOC entry 5972 (class 0 OID 0)
-- Dependencies: 372
-- Name: det_encuesta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.det_encuesta_id_seq OWNED BY public.det_encuesta.id;


--
-- TOC entry 230 (class 1259 OID 159349)
-- Name: det_movalmacen; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.det_movalmacen (
    id bigint NOT NULL,
    movalmacen_id bigint NOT NULL,
    producto_id bigint NOT NULL,
    json_producto json NOT NULL,
    unidad_id bigint NOT NULL,
    json_listaunidad json NOT NULL,
    cantidad numeric(24,6) NOT NULL,
    comentario text NOT NULL,
    situacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.det_movalmacen OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 159355)
-- Name: det_movalmacen_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.det_movalmacen_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.det_movalmacen_id_seq OWNER TO postgres;

--
-- TOC entry 5973 (class 0 OID 0)
-- Dependencies: 231
-- Name: det_movalmacen_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.det_movalmacen_id_seq OWNED BY public.det_movalmacen.id;


--
-- TOC entry 232 (class 1259 OID 159356)
-- Name: det_movcaja; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.det_movcaja (
    id bigint NOT NULL,
    movcaja_id bigint NOT NULL,
    tipo character varying(255) NOT NULL,
    fechavencimiento timestamp(0) without time zone,
    fechapago timestamp(0) without time zone,
    mediopago_id bigint NOT NULL,
    mediopago character varying(255) NOT NULL,
    numero character varying(255) NOT NULL,
    tarjeta_id bigint,
    tarjeta character varying(255) NOT NULL,
    banco_id bigint,
    banco character varying(255) NOT NULL,
    billeteradigital_id bigint,
    billeteradigital character varying(255) NOT NULL,
    pos_id bigint,
    pos character varying(255) NOT NULL,
    monto numeric(24,6) NOT NULL,
    comentario text NOT NULL,
    situacion character varying(1) DEFAULT 'A'::character varying NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.det_movcaja OWNER TO postgres;

--
-- TOC entry 5974 (class 0 OID 0)
-- Dependencies: 232
-- Name: COLUMN det_movcaja.tipo; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.det_movcaja.tipo IS 'PAGADO, DEUDA';


--
-- TOC entry 5975 (class 0 OID 0)
-- Dependencies: 232
-- Name: COLUMN det_movcaja.fechavencimiento; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.det_movcaja.fechavencimiento IS 'Cancelar en la fecha';


--
-- TOC entry 5976 (class 0 OID 0)
-- Dependencies: 232
-- Name: COLUMN det_movcaja.fechapago; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.det_movcaja.fechapago IS 'Pagado en la fecha';


--
-- TOC entry 233 (class 1259 OID 159362)
-- Name: det_movcaja_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.det_movcaja_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.det_movcaja_id_seq OWNER TO postgres;

--
-- TOC entry 5977 (class 0 OID 0)
-- Dependencies: 233
-- Name: det_movcaja_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.det_movcaja_id_seq OWNED BY public.det_movcaja.id;


--
-- TOC entry 234 (class 1259 OID 159363)
-- Name: det_movhabitacion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.det_movhabitacion (
    id bigint NOT NULL,
    tipo_detalle character varying(255) DEFAULT 'DETALLADO'::character varying NOT NULL,
    movhabitacion_id bigint NOT NULL,
    codigo character varying(255) NOT NULL,
    descripcion character varying(255) NOT NULL,
    producto_id bigint,
    json_producto json,
    json_medidaproducto json,
    unidad_id bigint NOT NULL,
    json_unidad json NOT NULL,
    igv_id bigint,
    json_igv json,
    cantidad numeric(24,6) NOT NULL,
    monto numeric(24,6) NOT NULL,
    dcto_porcentaje numeric(24,6) NOT NULL,
    monto_original numeric(24,6) NOT NULL,
    comentario text NOT NULL,
    det_categoria json NOT NULL,
    opc_categoria json NOT NULL,
    det_movventa_id bigint,
    complementos json DEFAULT '[]'::json NOT NULL,
    situacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.det_movhabitacion OWNER TO postgres;

--
-- TOC entry 5978 (class 0 OID 0)
-- Dependencies: 234
-- Name: COLUMN det_movhabitacion.tipo_detalle; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.det_movhabitacion.tipo_detalle IS 'DETALLADO, GLOSA';


--
-- TOC entry 235 (class 1259 OID 159371)
-- Name: det_movhabitacion_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.det_movhabitacion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.det_movhabitacion_id_seq OWNER TO postgres;

--
-- TOC entry 5979 (class 0 OID 0)
-- Dependencies: 235
-- Name: det_movhabitacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.det_movhabitacion_id_seq OWNED BY public.det_movhabitacion.id;


--
-- TOC entry 236 (class 1259 OID 159372)
-- Name: det_movventa; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.det_movventa (
    id bigint NOT NULL,
    tipo_detalle character varying(255) DEFAULT 'DETALLADO'::character varying NOT NULL,
    movventa_id bigint NOT NULL,
    codigo character varying(255) NOT NULL,
    descripcion character varying(255) NOT NULL,
    producto_id bigint,
    json_producto json,
    json_medidaproducto json,
    unidad_id bigint NOT NULL,
    json_unidad json NOT NULL,
    igv_id bigint,
    json_igv json,
    cantidad numeric(24,6) NOT NULL,
    monto numeric(24,6) NOT NULL,
    dcto_porcentaje numeric(24,6) NOT NULL,
    monto_original numeric(24,6) NOT NULL,
    comentario text NOT NULL,
    det_categoria json NOT NULL,
    opc_categoria json NOT NULL,
    det_movventa_id bigint,
    complementos json DEFAULT '[]'::json NOT NULL,
    situacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.det_movventa OWNER TO postgres;

--
-- TOC entry 5980 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN det_movventa.tipo_detalle; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.det_movventa.tipo_detalle IS 'DETALLADO, GLOSA';


--
-- TOC entry 237 (class 1259 OID 159380)
-- Name: det_movventa_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.det_movventa_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.det_movventa_id_seq OWNER TO postgres;

--
-- TOC entry 5981 (class 0 OID 0)
-- Dependencies: 237
-- Name: det_movventa_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.det_movventa_id_seq OWNED BY public.det_movventa.id;


--
-- TOC entry 377 (class 1259 OID 192188)
-- Name: det_respuesta; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.det_respuesta (
    id bigint NOT NULL,
    det_encuesta_id bigint NOT NULL,
    respuesta_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.det_respuesta OWNER TO postgres;

--
-- TOC entry 376 (class 1259 OID 192187)
-- Name: det_respuesta_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.det_respuesta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.det_respuesta_id_seq OWNER TO postgres;

--
-- TOC entry 5982 (class 0 OID 0)
-- Dependencies: 376
-- Name: det_respuesta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.det_respuesta_id_seq OWNED BY public.det_respuesta.id;


--
-- TOC entry 238 (class 1259 OID 159381)
-- Name: distribuidor; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.distribuidor (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.distribuidor OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 159384)
-- Name: distribuidor_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.distribuidor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.distribuidor_id_seq OWNER TO postgres;

--
-- TOC entry 5983 (class 0 OID 0)
-- Dependencies: 239
-- Name: distribuidor_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.distribuidor_id_seq OWNED BY public.distribuidor.id;


--
-- TOC entry 240 (class 1259 OID 159385)
-- Name: empresas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.empresas (
    id bigint NOT NULL,
    razon_social character varying(255) NOT NULL,
    direccion character varying(255),
    ruc character varying(11) NOT NULL,
    email character varying(255),
    telefono_fijo character varying(20),
    telefono_movil character varying(20),
    logo character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.empresas OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 159390)
-- Name: empresas_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.empresas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.empresas_id_seq OWNER TO postgres;

--
-- TOC entry 5984 (class 0 OID 0)
-- Dependencies: 241
-- Name: empresas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.empresas_id_seq OWNED BY public.empresas.id;


--
-- TOC entry 369 (class 1259 OID 192104)
-- Name: encuesta; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.encuesta (
    id bigint NOT NULL,
    uuid uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    nombre character varying(255) NOT NULL,
    descripcion text NOT NULL,
    fecha timestamp(0) without time zone NOT NULL,
    fechavencimiento timestamp(0) without time zone,
    mensaje_finalizacion text NOT NULL,
    imprimible character(1) DEFAULT 'N'::bpchar NOT NULL,
    estado character varying(255) DEFAULT 'VIGENTE'::character varying NOT NULL,
    situacion character(1) DEFAULT 'E'::bpchar NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.encuesta OWNER TO postgres;

--
-- TOC entry 368 (class 1259 OID 192103)
-- Name: encuesta_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.encuesta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.encuesta_id_seq OWNER TO postgres;

--
-- TOC entry 5985 (class 0 OID 0)
-- Dependencies: 368
-- Name: encuesta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.encuesta_id_seq OWNED BY public.encuesta.id;


--
-- TOC entry 242 (class 1259 OID 159391)
-- Name: facturador; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.facturador (
    id bigint NOT NULL,
    empresa_facturador_id bigint NOT NULL,
    porcentaje_igv numeric(8,2) DEFAULT '18'::numeric NOT NULL,
    username_wsdl character varying(255) NOT NULL,
    password_wsdl character varying(255) NOT NULL,
    modo_autenticacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    username_sunat character varying(255) NOT NULL,
    password_sunat character varying(255) NOT NULL,
    situacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.facturador OWNER TO postgres;

--
-- TOC entry 5986 (class 0 OID 0)
-- Dependencies: 242
-- Name: COLUMN facturador.empresa_facturador_id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.facturador.empresa_facturador_id IS 'Id de empresa en facturador';


--
-- TOC entry 243 (class 1259 OID 159399)
-- Name: facturador_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.facturador_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.facturador_id_seq OWNER TO postgres;

--
-- TOC entry 5987 (class 0 OID 0)
-- Dependencies: 243
-- Name: facturador_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.facturador_id_seq OWNED BY public.facturador.id;


--
-- TOC entry 244 (class 1259 OID 159400)
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- TOC entry 245 (class 1259 OID 159406)
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- TOC entry 5988 (class 0 OID 0)
-- Dependencies: 245
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- TOC entry 246 (class 1259 OID 159407)
-- Name: habitacion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.habitacion (
    id integer NOT NULL,
    numero character varying(50) NOT NULL,
    situacion character varying(500) NOT NULL,
    piso_id integer NOT NULL,
    tipohabitacion_id integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    sucursal_id bigint DEFAULT '39'::bigint NOT NULL,
    deleted_at timestamp(0) without time zone,
    descripcion character varying(255) NOT NULL
);


ALTER TABLE public.habitacion OWNER TO postgres;

--
-- TOC entry 247 (class 1259 OID 159413)
-- Name: habitacion_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.habitacion_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.habitacion_id_seq OWNER TO postgres;

--
-- TOC entry 5989 (class 0 OID 0)
-- Dependencies: 247
-- Name: habitacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.habitacion_id_seq OWNED BY public.habitacion.id;


--
-- TOC entry 365 (class 1259 OID 183884)
-- Name: historial_habitacion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.historial_habitacion (
    id bigint NOT NULL,
    situacion_pago character varying(1) DEFAULT 'N'::character varying NOT NULL,
    situacion_checkout character varying(1) DEFAULT 'N'::character varying NOT NULL,
    movhabitacion_id bigint NOT NULL,
    relturnocaja_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.historial_habitacion OWNER TO postgres;

--
-- TOC entry 364 (class 1259 OID 183883)
-- Name: historial_habitacion_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.historial_habitacion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.historial_habitacion_id_seq OWNER TO postgres;

--
-- TOC entry 5990 (class 0 OID 0)
-- Dependencies: 364
-- Name: historial_habitacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.historial_habitacion_id_seq OWNED BY public.historial_habitacion.id;


--
-- TOC entry 248 (class 1259 OID 159414)
-- Name: huesped; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.huesped (
    id bigint NOT NULL,
    persona_id bigint NOT NULL,
    movhabitacion_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.huesped OWNER TO postgres;

--
-- TOC entry 249 (class 1259 OID 159417)
-- Name: huesped_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.huesped_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.huesped_id_seq OWNER TO postgres;

--
-- TOC entry 5991 (class 0 OID 0)
-- Dependencies: 249
-- Name: huesped_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.huesped_id_seq OWNED BY public.huesped.id;


--
-- TOC entry 250 (class 1259 OID 159418)
-- Name: igv; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.igv (
    id bigint NOT NULL,
    codigo character varying(255) NOT NULL,
    descripcion character varying(255) NOT NULL,
    porcentaje_igv double precision NOT NULL,
    orden integer NOT NULL,
    situacion boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.igv OWNER TO postgres;

--
-- TOC entry 251 (class 1259 OID 159424)
-- Name: igv_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.igv_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.igv_id_seq OWNER TO postgres;

--
-- TOC entry 5992 (class 0 OID 0)
-- Dependencies: 251
-- Name: igv_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.igv_id_seq OWNED BY public.igv.id;


--
-- TOC entry 252 (class 1259 OID 159425)
-- Name: kardex; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.kardex (
    id bigint NOT NULL,
    detalle_id bigint,
    producto_id bigint NOT NULL,
    json_producto json NOT NULL,
    unidad_id bigint NOT NULL,
    json_listaunidad json NOT NULL,
    cantidad numeric(24,6) NOT NULL,
    unidadbase_id bigint NOT NULL,
    cantidadbase numeric(24,6) NOT NULL,
    preciounitario numeric(24,6) NOT NULL,
    moneda character varying(255) DEFAULT 'PEN'::character varying NOT NULL,
    tipocambio numeric(8,3) NOT NULL,
    total numeric(24,6) NOT NULL,
    fecha timestamp(0) without time zone NOT NULL,
    situacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    usuario_id bigint NOT NULL,
    usuario character varying(255) NOT NULL,
    movimiento_id bigint NOT NULL,
    tipomovimiento_id bigint NOT NULL,
    tipodocumento_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    stockanterior numeric(24,6) DEFAULT '0'::numeric NOT NULL,
    stockactual numeric(24,6) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.kardex OWNER TO postgres;

--
-- TOC entry 253 (class 1259 OID 159434)
-- Name: kardex_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.kardex_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.kardex_id_seq OWNER TO postgres;

--
-- TOC entry 5993 (class 0 OID 0)
-- Dependencies: 253
-- Name: kardex_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.kardex_id_seq OWNED BY public.kardex.id;


--
-- TOC entry 254 (class 1259 OID 159435)
-- Name: listaunidad; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.listaunidad (
    id bigint NOT NULL,
    formula numeric(24,6) NOT NULL,
    producto_id bigint NOT NULL,
    unidad_id bigint NOT NULL,
    unidadbase_id bigint NOT NULL,
    empresa_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.listaunidad OWNER TO postgres;

--
-- TOC entry 255 (class 1259 OID 159438)
-- Name: listaunidad_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.listaunidad_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.listaunidad_id_seq OWNER TO postgres;

--
-- TOC entry 5994 (class 0 OID 0)
-- Dependencies: 255
-- Name: listaunidad_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.listaunidad_id_seq OWNED BY public.listaunidad.id;


--
-- TOC entry 256 (class 1259 OID 159439)
-- Name: marca; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.marca (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    abreviatura character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.marca OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 159444)
-- Name: marca_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.marca_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.marca_id_seq OWNER TO postgres;

--
-- TOC entry 5995 (class 0 OID 0)
-- Dependencies: 257
-- Name: marca_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.marca_id_seq OWNED BY public.marca.id;


--
-- TOC entry 258 (class 1259 OID 159445)
-- Name: mediopago; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mediopago (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    orden integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    situacion boolean DEFAULT true
);


ALTER TABLE public.mediopago OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 159449)
-- Name: mediopago_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.mediopago_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.mediopago_id_seq OWNER TO postgres;

--
-- TOC entry 5996 (class 0 OID 0)
-- Dependencies: 259
-- Name: mediopago_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mediopago_id_seq OWNED BY public.mediopago.id;


--
-- TOC entry 260 (class 1259 OID 159450)
-- Name: menuopciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.menuopciones (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    icono text NOT NULL,
    accion character varying(255) NOT NULL,
    tabla_id bigint NOT NULL,
    modulo_id bigint NOT NULL,
    tipoopcion_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    acceso_rapido character(1) DEFAULT 'N'::bpchar NOT NULL,
    condiciones_ar json DEFAULT '[]'::json NOT NULL,
    estado boolean DEFAULT true
);


ALTER TABLE public.menuopciones OWNER TO postgres;

--
-- TOC entry 5997 (class 0 OID 0)
-- Dependencies: 260
-- Name: COLUMN menuopciones.accion; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.menuopciones.accion IS 'Nombre de ruta';


--
-- TOC entry 5998 (class 0 OID 0)
-- Dependencies: 260
-- Name: COLUMN menuopciones.acceso_rapido; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.menuopciones.acceso_rapido IS 'S => Sí, N => No';


--
-- TOC entry 261 (class 1259 OID 159459)
-- Name: menuopciones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.menuopciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.menuopciones_id_seq OWNER TO postgres;

--
-- TOC entry 5999 (class 0 OID 0)
-- Dependencies: 261
-- Name: menuopciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.menuopciones_id_seq OWNED BY public.menuopciones.id;


--
-- TOC entry 262 (class 1259 OID 159460)
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- TOC entry 263 (class 1259 OID 159463)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- TOC entry 6000 (class 0 OID 0)
-- Dependencies: 263
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 264 (class 1259 OID 159464)
-- Name: modulos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.modulos (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    abreviatura character varying(20) NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    icono text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.modulos OWNER TO postgres;

--
-- TOC entry 265 (class 1259 OID 159470)
-- Name: modulos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.modulos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.modulos_id_seq OWNER TO postgres;

--
-- TOC entry 6001 (class 0 OID 0)
-- Dependencies: 265
-- Name: modulos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.modulos_id_seq OWNED BY public.modulos.id;


--
-- TOC entry 266 (class 1259 OID 159471)
-- Name: motivomovimiento; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.motivomovimiento (
    id bigint NOT NULL,
    codigo character varying(255) NOT NULL,
    descripcion character varying(255) NOT NULL,
    tipomovimiento_id bigint NOT NULL,
    orden integer NOT NULL,
    situacion boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.motivomovimiento OWNER TO postgres;

--
-- TOC entry 267 (class 1259 OID 159477)
-- Name: motivomovimiento_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.motivomovimiento_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.motivomovimiento_id_seq OWNER TO postgres;

--
-- TOC entry 6002 (class 0 OID 0)
-- Dependencies: 267
-- Name: motivomovimiento_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.motivomovimiento_id_seq OWNED BY public.motivomovimiento.id;


--
-- TOC entry 268 (class 1259 OID 159478)
-- Name: movalmacen; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.movalmacen (
    id bigint NOT NULL,
    estado character varying(255) DEFAULT 'FINALIZADO'::character varying NOT NULL,
    movimiento_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.movalmacen OWNER TO postgres;

--
-- TOC entry 6003 (class 0 OID 0)
-- Dependencies: 268
-- Name: COLUMN movalmacen.estado; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.movalmacen.estado IS 'PENDIENTE, ENVIADO, FINALIZADO, RECHAZADO';


--
-- TOC entry 269 (class 1259 OID 159482)
-- Name: movalmacen_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.movalmacen_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.movalmacen_id_seq OWNER TO postgres;

--
-- TOC entry 6004 (class 0 OID 0)
-- Dependencies: 269
-- Name: movalmacen_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.movalmacen_id_seq OWNED BY public.movalmacen.id;


--
-- TOC entry 270 (class 1259 OID 159483)
-- Name: movcaja; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.movcaja (
    id bigint NOT NULL,
    conceptopago_id bigint NOT NULL,
    moneda character varying(255) DEFAULT 'PEN'::character varying NOT NULL,
    tipocambio numeric(8,3) NOT NULL,
    total numeric(8,2) NOT NULL,
    caja_id bigint NOT NULL,
    caja character varying(255) NOT NULL,
    turno_id bigint NOT NULL,
    json_turno json NOT NULL,
    movimiento_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.movcaja OWNER TO postgres;

--
-- TOC entry 271 (class 1259 OID 159489)
-- Name: movcaja_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.movcaja_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.movcaja_id_seq OWNER TO postgres;

--
-- TOC entry 6005 (class 0 OID 0)
-- Dependencies: 271
-- Name: movcaja_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.movcaja_id_seq OWNED BY public.movcaja.id;


--
-- TOC entry 272 (class 1259 OID 159490)
-- Name: movhabitacion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.movhabitacion (
    id bigint NOT NULL,
    descuento numeric(10,2),
    fechaingreso timestamp(0) without time zone NOT NULL,
    fechasalida timestamp(0) without time zone,
    dias numeric(10,2),
    total numeric(10,2),
    preciohabitacion numeric(10,4),
    situacion character varying(1) DEFAULT 'A'::character varying NOT NULL,
    comentario character varying(255),
    moneda character varying(255) DEFAULT 'PEN'::character varying NOT NULL,
    habitacion_id bigint NOT NULL,
    reserva_id bigint,
    movimiento_id bigint NOT NULL,
    early_checkin numeric(10,2),
    late_checkout numeric(10,2),
    day_use numeric(10,2),
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    fecha_salida_real timestamp(0) without time zone,
    horas integer,
    modalidad character(1)
);


ALTER TABLE public.movhabitacion OWNER TO postgres;

--
-- TOC entry 273 (class 1259 OID 159497)
-- Name: movhabitacion_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.movhabitacion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.movhabitacion_id_seq OWNER TO postgres;

--
-- TOC entry 6006 (class 0 OID 0)
-- Dependencies: 273
-- Name: movhabitacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.movhabitacion_id_seq OWNED BY public.movhabitacion.id;


--
-- TOC entry 274 (class 1259 OID 159498)
-- Name: movimiento; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.movimiento (
    id bigint NOT NULL,
    numero character varying(255) NOT NULL,
    fecha timestamp(0) without time zone NOT NULL,
    usuario_id bigint NOT NULL,
    usuario character varying(255) NOT NULL,
    persona_id bigint,
    persona character varying(255) NOT NULL,
    responsable_id bigint NOT NULL,
    responsable character varying(255) NOT NULL,
    comentario text NOT NULL,
    situacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    tipomovimiento_id bigint NOT NULL,
    tipodocumento_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    movimiento_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.movimiento OWNER TO postgres;

--
-- TOC entry 275 (class 1259 OID 159504)
-- Name: movimiento_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.movimiento_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.movimiento_id_seq OWNER TO postgres;

--
-- TOC entry 6007 (class 0 OID 0)
-- Dependencies: 275
-- Name: movimiento_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.movimiento_id_seq OWNED BY public.movimiento.id;


--
-- TOC entry 276 (class 1259 OID 159505)
-- Name: movventa; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.movventa (
    id bigint NOT NULL,
    json_sucursal json NOT NULL,
    serie character varying(255) NOT NULL,
    anio character varying(255) NOT NULL,
    tipo_detalle character varying(255) DEFAULT 'DETALLADO'::character varying NOT NULL,
    consumo character varying(1) DEFAULT 'N'::character varying NOT NULL,
    tipo_pago character varying(255) DEFAULT 'CONTADO'::character varying NOT NULL,
    estado character varying(255) DEFAULT ''::character varying NOT NULL,
    tipo_venta character varying(255) DEFAULT 'MINORISTA'::character varying NOT NULL,
    moneda character varying(255) DEFAULT 'PEN'::character varying NOT NULL,
    tipocambio numeric(8,3) NOT NULL,
    subtotal numeric(8,2) NOT NULL,
    igv numeric(8,2) NOT NULL,
    total numeric(8,2) NOT NULL,
    movimiento_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.movventa OWNER TO postgres;

--
-- TOC entry 6008 (class 0 OID 0)
-- Dependencies: 276
-- Name: COLUMN movventa.tipo_detalle; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.movventa.tipo_detalle IS 'DETALLADO, GLOSA';


--
-- TOC entry 6009 (class 0 OID 0)
-- Dependencies: 276
-- Name: COLUMN movventa.consumo; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.movventa.consumo IS 'S => Venta por consumo, N => Venta detallada';


--
-- TOC entry 6010 (class 0 OID 0)
-- Dependencies: 276
-- Name: COLUMN movventa.tipo_pago; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.movventa.tipo_pago IS 'CONTADO, CREDITO';


--
-- TOC entry 6011 (class 0 OID 0)
-- Dependencies: 276
-- Name: COLUMN movventa.estado; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.movventa.estado IS 'Estado de la venta respecto a SUNAT, en caso sea factura y/o boleta';


--
-- TOC entry 6012 (class 0 OID 0)
-- Dependencies: 276
-- Name: COLUMN movventa.tipo_venta; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.movventa.tipo_venta IS 'MINORISTA, MAYORISTA';


--
-- TOC entry 6013 (class 0 OID 0)
-- Dependencies: 276
-- Name: COLUMN movventa.total; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.movventa.total IS 'subtotal + igv';


--
-- TOC entry 277 (class 1259 OID 159516)
-- Name: movventa_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.movventa_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.movventa_id_seq OWNER TO postgres;

--
-- TOC entry 6014 (class 0 OID 0)
-- Dependencies: 277
-- Name: movventa_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.movventa_id_seq OWNED BY public.movventa.id;


--
-- TOC entry 278 (class 1259 OID 159517)
-- Name: operaciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.operaciones (
    id bigint NOT NULL,
    tipo character varying(1) DEFAULT 'T'::character varying NOT NULL,
    descripcion character varying(255) NOT NULL,
    comentario character varying(255) NOT NULL,
    accion character varying(255) NOT NULL,
    imagen text NOT NULL,
    color character varying(255) NOT NULL,
    parametros text DEFAULT '{}'::text NOT NULL,
    tabla_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    estado boolean DEFAULT true
);


ALTER TABLE public.operaciones OWNER TO postgres;

--
-- TOC entry 279 (class 1259 OID 159525)
-- Name: operaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.operaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.operaciones_id_seq OWNER TO postgres;

--
-- TOC entry 6015 (class 0 OID 0)
-- Dependencies: 279
-- Name: operaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.operaciones_id_seq OWNED BY public.operaciones.id;


--
-- TOC entry 280 (class 1259 OID 159526)
-- Name: parametros; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.parametros (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    valor character varying(255) NOT NULL,
    tipo character varying(255) DEFAULT 'EMPRESA'::character varying NOT NULL,
    obligatorio character varying(1) DEFAULT 'N'::character varying NOT NULL,
    estado boolean DEFAULT true NOT NULL,
    categoriaparametro_id bigint NOT NULL,
    tabla_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.parametros OWNER TO postgres;

--
-- TOC entry 281 (class 1259 OID 159534)
-- Name: parametros_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.parametros_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.parametros_id_seq OWNER TO postgres;

--
-- TOC entry 6016 (class 0 OID 0)
-- Dependencies: 281
-- Name: parametros_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.parametros_id_seq OWNED BY public.parametros.id;


--
-- TOC entry 282 (class 1259 OID 159535)
-- Name: pasarelapago; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pasarelapago (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    orden integer NOT NULL,
    situacion boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.pasarelapago OWNER TO postgres;

--
-- TOC entry 283 (class 1259 OID 159539)
-- Name: pasarelapago_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pasarelapago_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pasarelapago_id_seq OWNER TO postgres;

--
-- TOC entry 6017 (class 0 OID 0)
-- Dependencies: 283
-- Name: pasarelapago_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pasarelapago_id_seq OWNED BY public.pasarelapago.id;


--
-- TOC entry 284 (class 1259 OID 159540)
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- TOC entry 285 (class 1259 OID 159545)
-- Name: perfiles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.perfiles (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    abreviatura character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.perfiles OWNER TO postgres;

--
-- TOC entry 286 (class 1259 OID 159550)
-- Name: perfiles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.perfiles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.perfiles_id_seq OWNER TO postgres;

--
-- TOC entry 6018 (class 0 OID 0)
-- Dependencies: 286
-- Name: perfiles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.perfiles_id_seq OWNED BY public.perfiles.id;


--
-- TOC entry 287 (class 1259 OID 159551)
-- Name: perfilsucursal; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.perfilsucursal (
    id bigint NOT NULL,
    perfil_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.perfilsucursal OWNER TO postgres;

--
-- TOC entry 288 (class 1259 OID 159554)
-- Name: perfilsucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.perfilsucursal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.perfilsucursal_id_seq OWNER TO postgres;

--
-- TOC entry 6019 (class 0 OID 0)
-- Dependencies: 288
-- Name: perfilsucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.perfilsucursal_id_seq OWNED BY public.perfilsucursal.id;


--
-- TOC entry 289 (class 1259 OID 159555)
-- Name: permisousuario; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permisousuario (
    id bigint NOT NULL,
    situacion boolean DEFAULT true NOT NULL,
    menuopcion_id bigint NOT NULL,
    perfil_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.permisousuario OWNER TO postgres;

--
-- TOC entry 290 (class 1259 OID 159559)
-- Name: permisousuario_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permisousuario_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permisousuario_id_seq OWNER TO postgres;

--
-- TOC entry 6020 (class 0 OID 0)
-- Dependencies: 290
-- Name: permisousuario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permisousuario_id_seq OWNED BY public.permisousuario.id;


--
-- TOC entry 291 (class 1259 OID 159560)
-- Name: persona; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.persona (
    id bigint NOT NULL,
    nombres character varying(255) NOT NULL,
    apellidos character varying(255),
    direccion character varying(255) NOT NULL,
    email character varying(255),
    telefonofijo character varying(10) NOT NULL,
    telefonomovil character varying(12) NOT NULL,
    imagen text NOT NULL,
    compartido character varying(1) DEFAULT 'N'::character varying NOT NULL,
    personamaestro_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    distrito_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.persona OWNER TO postgres;

--
-- TOC entry 292 (class 1259 OID 159566)
-- Name: persona_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.persona_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.persona_id_seq OWNER TO postgres;

--
-- TOC entry 6021 (class 0 OID 0)
-- Dependencies: 292
-- Name: persona_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.persona_id_seq OWNED BY public.persona.id;


--
-- TOC entry 293 (class 1259 OID 159567)
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO postgres;

--
-- TOC entry 294 (class 1259 OID 159572)
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO postgres;

--
-- TOC entry 6022 (class 0 OID 0)
-- Dependencies: 294
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- TOC entry 295 (class 1259 OID 159573)
-- Name: personamaestro; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personamaestro (
    id bigint NOT NULL,
    tipopersona character varying(255) NOT NULL,
    nombres character varying(255) NOT NULL,
    apellidos character varying(255) NOT NULL,
    nrodoc character varying(11) NOT NULL,
    sexo character varying(1) DEFAULT 'M'::character varying NOT NULL,
    fechanac date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.personamaestro OWNER TO postgres;

--
-- TOC entry 296 (class 1259 OID 159579)
-- Name: personamaestro_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personamaestro_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personamaestro_id_seq OWNER TO postgres;

--
-- TOC entry 6023 (class 0 OID 0)
-- Dependencies: 296
-- Name: personamaestro_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personamaestro_id_seq OWNED BY public.personamaestro.id;


--
-- TOC entry 297 (class 1259 OID 159580)
-- Name: piso; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.piso (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    estado boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    sucursal_id bigint DEFAULT '39'::bigint NOT NULL,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.piso OWNER TO postgres;

--
-- TOC entry 298 (class 1259 OID 159585)
-- Name: piso_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.piso_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.piso_id_seq OWNER TO postgres;

--
-- TOC entry 6024 (class 0 OID 0)
-- Dependencies: 298
-- Name: piso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.piso_id_seq OWNED BY public.piso.id;


--
-- TOC entry 299 (class 1259 OID 159586)
-- Name: producto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.producto (
    id bigint NOT NULL,
    codigo character varying(50) NOT NULL,
    descripcion character varying(255) NOT NULL,
    abreviatura character varying(255) NOT NULL,
    tipo character varying(255) NOT NULL,
    categoria_id bigint NOT NULL,
    marca_id bigint,
    unidadbase_id bigint NOT NULL,
    medidapeso_id bigint,
    kardex character varying(1) DEFAULT 'N'::character varying NOT NULL,
    compuesto character varying(1) DEFAULT 'N'::character varying NOT NULL,
    autoproduccion character varying(255) DEFAULT 'S'::character varying NOT NULL,
    peso numeric(24,6) NOT NULL,
    comentario text NOT NULL,
    imagen text NOT NULL,
    complemento character varying(255) DEFAULT 'NO'::character varying NOT NULL,
    modocomplemento character varying(255) DEFAULT ''::character varying NOT NULL,
    empresa_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    clasificacion character varying(255) DEFAULT 'BIEN'::character varying NOT NULL,
    nro_parte character varying(255) DEFAULT ''::character varying NOT NULL,
    caracteristicas text DEFAULT ''::text NOT NULL,
    distribuidor_id bigint,
    imagen2 text DEFAULT ''::text NOT NULL,
    imagen3 text DEFAULT ''::text NOT NULL
);


ALTER TABLE public.producto OWNER TO postgres;

--
-- TOC entry 6025 (class 0 OID 0)
-- Dependencies: 299
-- Name: COLUMN producto.tipo; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.producto.tipo IS 'PRODUCTO, COMPONENTE';


--
-- TOC entry 6026 (class 0 OID 0)
-- Dependencies: 299
-- Name: COLUMN producto.autoproduccion; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.producto.autoproduccion IS 'S => Sí, N => No';


--
-- TOC entry 6027 (class 0 OID 0)
-- Dependencies: 299
-- Name: COLUMN producto.complemento; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.producto.complemento IS 'NO => No permite complementos, HAS => Tiene complementos, IS => Es complemento';


--
-- TOC entry 6028 (class 0 OID 0)
-- Dependencies: 299
-- Name: COLUMN producto.modocomplemento; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.producto.modocomplemento IS 'TODO => Los mismos complementos son gratis, CANTIDAD => La cantidad de complementos es gratis';


--
-- TOC entry 6029 (class 0 OID 0)
-- Dependencies: 299
-- Name: COLUMN producto.clasificacion; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.producto.clasificacion IS 'BIEN, SERVICIO';


--
-- TOC entry 300 (class 1259 OID 159601)
-- Name: producto_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.producto_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.producto_id_seq OWNER TO postgres;

--
-- TOC entry 6030 (class 0 OID 0)
-- Dependencies: 300
-- Name: producto_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.producto_id_seq OWNED BY public.producto.id;


--
-- TOC entry 301 (class 1259 OID 159602)
-- Name: rel_camposucursal; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_camposucursal (
    id bigint NOT NULL,
    tipo character varying(1) DEFAULT 'G'::character varying NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    situacion boolean DEFAULT true NOT NULL,
    campo_id bigint NOT NULL,
    tabla_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_camposucursal OWNER TO postgres;

--
-- TOC entry 302 (class 1259 OID 159608)
-- Name: rel_camposucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_camposucursal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_camposucursal_id_seq OWNER TO postgres;

--
-- TOC entry 6031 (class 0 OID 0)
-- Dependencies: 302
-- Name: rel_camposucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_camposucursal_id_seq OWNED BY public.rel_camposucursal.id;


--
-- TOC entry 303 (class 1259 OID 159609)
-- Name: rel_categoriasucursal; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_categoriasucursal (
    id bigint NOT NULL,
    tipocarta character varying(255) NOT NULL,
    situacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    categoria_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_categoriasucursal OWNER TO postgres;

--
-- TOC entry 6032 (class 0 OID 0)
-- Dependencies: 303
-- Name: COLUMN rel_categoriasucursal.tipocarta; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.rel_categoriasucursal.tipocarta IS 'PLATOS A LA CARTA';


--
-- TOC entry 304 (class 1259 OID 159613)
-- Name: rel_categoriasucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_categoriasucursal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_categoriasucursal_id_seq OWNER TO postgres;

--
-- TOC entry 6033 (class 0 OID 0)
-- Dependencies: 304
-- Name: rel_categoriasucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_categoriasucursal_id_seq OWNED BY public.rel_categoriasucursal.id;


--
-- TOC entry 305 (class 1259 OID 159614)
-- Name: rel_listaunidadsucursal; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_listaunidadsucursal (
    id bigint NOT NULL,
    preciocompra numeric(24,6) NOT NULL,
    preciomanoobra numeric(24,6) NOT NULL,
    precioventa numeric(24,6) NOT NULL,
    moneda character varying(255) DEFAULT 'PEN'::character varying NOT NULL,
    listaunidad_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_listaunidadsucursal OWNER TO postgres;

--
-- TOC entry 306 (class 1259 OID 159618)
-- Name: rel_listaunidadsucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_listaunidadsucursal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_listaunidadsucursal_id_seq OWNER TO postgres;

--
-- TOC entry 6034 (class 0 OID 0)
-- Dependencies: 306
-- Name: rel_listaunidadsucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_listaunidadsucursal_id_seq OWNED BY public.rel_listaunidadsucursal.id;


--
-- TOC entry 307 (class 1259 OID 159619)
-- Name: rel_mediopagopasarela; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_mediopagopasarela (
    id bigint NOT NULL,
    pasarela_id bigint NOT NULL,
    medio_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_mediopagopasarela OWNER TO postgres;

--
-- TOC entry 308 (class 1259 OID 159622)
-- Name: rel_mediopagopasarela_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_mediopagopasarela_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_mediopagopasarela_id_seq OWNER TO postgres;

--
-- TOC entry 6035 (class 0 OID 0)
-- Dependencies: 308
-- Name: rel_mediopagopasarela_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_mediopagopasarela_id_seq OWNED BY public.rel_mediopagopasarela.id;


--
-- TOC entry 309 (class 1259 OID 159623)
-- Name: rel_operacionsucursal; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_operacionsucursal (
    id bigint NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    situacion boolean DEFAULT true NOT NULL,
    operacion_id bigint NOT NULL,
    tabla_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_operacionsucursal OWNER TO postgres;

--
-- TOC entry 310 (class 1259 OID 159628)
-- Name: rel_operacionsucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_operacionsucursal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_operacionsucursal_id_seq OWNER TO postgres;

--
-- TOC entry 6036 (class 0 OID 0)
-- Dependencies: 310
-- Name: rel_operacionsucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_operacionsucursal_id_seq OWNED BY public.rel_operacionsucursal.id;


--
-- TOC entry 311 (class 1259 OID 159629)
-- Name: rel_operacionsucursalperfil; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_operacionsucursalperfil (
    id bigint NOT NULL,
    situacion boolean DEFAULT true NOT NULL,
    operacion_id bigint NOT NULL,
    tabla_id bigint NOT NULL,
    perfil_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_operacionsucursalperfil OWNER TO postgres;

--
-- TOC entry 312 (class 1259 OID 159633)
-- Name: rel_operacionsucursalperfil_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_operacionsucursalperfil_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_operacionsucursalperfil_id_seq OWNER TO postgres;

--
-- TOC entry 6037 (class 0 OID 0)
-- Dependencies: 312
-- Name: rel_operacionsucursalperfil_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_operacionsucursalperfil_id_seq OWNED BY public.rel_operacionsucursalperfil.id;


--
-- TOC entry 313 (class 1259 OID 159634)
-- Name: rel_parametroempresa; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_parametroempresa (
    id bigint NOT NULL,
    valor character varying(255) NOT NULL,
    parametro_id bigint NOT NULL,
    empresa_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_parametroempresa OWNER TO postgres;

--
-- TOC entry 314 (class 1259 OID 159637)
-- Name: rel_parametroempresa_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_parametroempresa_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_parametroempresa_id_seq OWNER TO postgres;

--
-- TOC entry 6038 (class 0 OID 0)
-- Dependencies: 314
-- Name: rel_parametroempresa_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_parametroempresa_id_seq OWNED BY public.rel_parametroempresa.id;


--
-- TOC entry 315 (class 1259 OID 159638)
-- Name: rel_parametrosucursal; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_parametrosucursal (
    id bigint NOT NULL,
    valor character varying(255) NOT NULL,
    parametro_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_parametrosucursal OWNER TO postgres;

--
-- TOC entry 316 (class 1259 OID 159641)
-- Name: rel_parametrosucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_parametrosucursal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_parametrosucursal_id_seq OWNER TO postgres;

--
-- TOC entry 6039 (class 0 OID 0)
-- Dependencies: 316
-- Name: rel_parametrosucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_parametrosucursal_id_seq OWNED BY public.rel_parametrosucursal.id;


--
-- TOC entry 317 (class 1259 OID 159642)
-- Name: rel_productosucursal; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_productosucursal (
    id bigint NOT NULL,
    situacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    fechavencimiento date,
    stockminimo numeric(24,6) NOT NULL,
    stockmaximo numeric(24,6) NOT NULL,
    stockoptimo numeric(24,6) NOT NULL,
    minimovender numeric(24,6) NOT NULL,
    minimocomprar numeric(24,6) NOT NULL,
    ubicacion_id bigint,
    fila integer DEFAULT 0 NOT NULL,
    columna integer DEFAULT 0 NOT NULL,
    favorito character varying(1) DEFAULT 'N'::character varying NOT NULL,
    subalmacen_id bigint,
    igv_id bigint,
    venta_unitaria character varying(255) DEFAULT 'N'::character varying NOT NULL,
    producto_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    duracion numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    proveedor_id bigint
);


ALTER TABLE public.rel_productosucursal OWNER TO postgres;

--
-- TOC entry 6040 (class 0 OID 0)
-- Dependencies: 317
-- Name: COLUMN rel_productosucursal.venta_unitaria; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.rel_productosucursal.venta_unitaria IS 'S => Sí, N => No';


--
-- TOC entry 6041 (class 0 OID 0)
-- Dependencies: 317
-- Name: COLUMN rel_productosucursal.duracion; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.rel_productosucursal.duracion IS 'minutos';


--
-- TOC entry 318 (class 1259 OID 159651)
-- Name: rel_productosucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_productosucursal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_productosucursal_id_seq OWNER TO postgres;

--
-- TOC entry 6042 (class 0 OID 0)
-- Dependencies: 318
-- Name: rel_productosucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_productosucursal_id_seq OWNED BY public.rel_productosucursal.id;


--
-- TOC entry 381 (class 1259 OID 200291)
-- Name: rel_seccionopcionmenu; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_seccionopcionmenu (
    id bigint NOT NULL,
    seccion_id bigint NOT NULL,
    opcionmenu_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_seccionopcionmenu OWNER TO postgres;

--
-- TOC entry 380 (class 1259 OID 200290)
-- Name: rel_seccionopcionmenu_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_seccionopcionmenu_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_seccionopcionmenu_id_seq OWNER TO postgres;

--
-- TOC entry 6043 (class 0 OID 0)
-- Dependencies: 380
-- Name: rel_seccionopcionmenu_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_seccionopcionmenu_id_seq OWNED BY public.rel_seccionopcionmenu.id;


--
-- TOC entry 319 (class 1259 OID 159652)
-- Name: rel_seccionsucursal; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_seccionsucursal (
    id bigint NOT NULL,
    situacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    seccion_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_seccionsucursal OWNER TO postgres;

--
-- TOC entry 320 (class 1259 OID 159656)
-- Name: rel_seccionsucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_seccionsucursal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_seccionsucursal_id_seq OWNER TO postgres;

--
-- TOC entry 6044 (class 0 OID 0)
-- Dependencies: 320
-- Name: rel_seccionsucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_seccionsucursal_id_seq OWNED BY public.rel_seccionsucursal.id;


--
-- TOC entry 321 (class 1259 OID 159657)
-- Name: rel_seccionsucursalperfil; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_seccionsucursalperfil (
    id bigint NOT NULL,
    perfil_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    seccion_id bigint NOT NULL,
    menuopcionesdefecto_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_seccionsucursalperfil OWNER TO postgres;

--
-- TOC entry 322 (class 1259 OID 159660)
-- Name: rel_seccionsucursalperfil_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_seccionsucursalperfil_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_seccionsucursalperfil_id_seq OWNER TO postgres;

--
-- TOC entry 6045 (class 0 OID 0)
-- Dependencies: 322
-- Name: rel_seccionsucursalperfil_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_seccionsucursalperfil_id_seq OWNED BY public.rel_seccionsucursalperfil.id;


--
-- TOC entry 323 (class 1259 OID 159661)
-- Name: rel_tablasucursal; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_tablasucursal (
    id bigint NOT NULL,
    tabla_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_tablasucursal OWNER TO postgres;

--
-- TOC entry 324 (class 1259 OID 159664)
-- Name: rel_tablasucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_tablasucursal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_tablasucursal_id_seq OWNER TO postgres;

--
-- TOC entry 6046 (class 0 OID 0)
-- Dependencies: 324
-- Name: rel_tablasucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_tablasucursal_id_seq OWNED BY public.rel_tablasucursal.id;


--
-- TOC entry 325 (class 1259 OID 159665)
-- Name: rel_turnocaja; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rel_turnocaja (
    id bigint NOT NULL,
    fechainicio timestamp(0) without time zone NOT NULL,
    fechafin timestamp(0) without time zone,
    situacion character varying(1) DEFAULT 'A'::character varying NOT NULL,
    movcajainicio_id bigint NOT NULL,
    movcajafin_id bigint,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rel_turnocaja OWNER TO postgres;

--
-- TOC entry 326 (class 1259 OID 159669)
-- Name: rel_turnocaja_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rel_turnocaja_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rel_turnocaja_id_seq OWNER TO postgres;

--
-- TOC entry 6047 (class 0 OID 0)
-- Dependencies: 326
-- Name: rel_turnocaja_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rel_turnocaja_id_seq OWNED BY public.rel_turnocaja.id;


--
-- TOC entry 327 (class 1259 OID 159670)
-- Name: reserva; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.reserva (
    id bigint NOT NULL,
    fecha timestamp(0) without time zone NOT NULL,
    fechasalida timestamp(0) without time zone,
    observacion character varying(200),
    situacion character varying(1) DEFAULT 'A'::character varying NOT NULL,
    persona_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    habitacion_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.reserva OWNER TO postgres;

--
-- TOC entry 328 (class 1259 OID 159674)
-- Name: reserva_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.reserva_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.reserva_id_seq OWNER TO postgres;

--
-- TOC entry 6048 (class 0 OID 0)
-- Dependencies: 328
-- Name: reserva_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.reserva_id_seq OWNED BY public.reserva.id;


--
-- TOC entry 375 (class 1259 OID 192164)
-- Name: respuesta; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.respuesta (
    id bigint NOT NULL,
    codigo character varying(255) NOT NULL,
    fecha timestamp(0) without time zone NOT NULL,
    persona_id bigint NOT NULL,
    persona character varying(255) NOT NULL,
    encuesta_id bigint NOT NULL,
    movimiento_id bigint,
    movimiento json,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.respuesta OWNER TO postgres;

--
-- TOC entry 374 (class 1259 OID 192163)
-- Name: respuesta_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.respuesta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.respuesta_id_seq OWNER TO postgres;

--
-- TOC entry 6049 (class 0 OID 0)
-- Dependencies: 374
-- Name: respuesta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.respuesta_id_seq OWNED BY public.respuesta.id;


--
-- TOC entry 329 (class 1259 OID 159675)
-- Name: rol; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rol (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rol OWNER TO postgres;

--
-- TOC entry 330 (class 1259 OID 159678)
-- Name: rol_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rol_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.rol_id_seq OWNER TO postgres;

--
-- TOC entry 6050 (class 0 OID 0)
-- Dependencies: 330
-- Name: rol_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rol_id_seq OWNED BY public.rol.id;


--
-- TOC entry 331 (class 1259 OID 159679)
-- Name: rolpersona; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rolpersona (
    persona_id bigint NOT NULL,
    rol_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.rolpersona OWNER TO postgres;

--
-- TOC entry 332 (class 1259 OID 159682)
-- Name: secciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.secciones (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    abreviatura character varying(255) NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    icono text NOT NULL,
    color character varying(255) NOT NULL,
    subtitulo text NOT NULL,
    comentario text NOT NULL,
    situacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.secciones OWNER TO postgres;

--
-- TOC entry 6051 (class 0 OID 0)
-- Dependencies: 332
-- Name: COLUMN secciones.descripcion; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.secciones.descripcion IS 'Descripción de la sección';


--
-- TOC entry 6052 (class 0 OID 0)
-- Dependencies: 332
-- Name: COLUMN secciones.abreviatura; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.secciones.abreviatura IS 'Abreviatura de la sección';


--
-- TOC entry 6053 (class 0 OID 0)
-- Dependencies: 332
-- Name: COLUMN secciones.orden; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.secciones.orden IS 'Orden de la sección';


--
-- TOC entry 6054 (class 0 OID 0)
-- Dependencies: 332
-- Name: COLUMN secciones.icono; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.secciones.icono IS 'Icono de la sección';


--
-- TOC entry 6055 (class 0 OID 0)
-- Dependencies: 332
-- Name: COLUMN secciones.color; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.secciones.color IS 'Color asociado a la sección';


--
-- TOC entry 6056 (class 0 OID 0)
-- Dependencies: 332
-- Name: COLUMN secciones.subtitulo; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.secciones.subtitulo IS 'Subtítulo de la sección';


--
-- TOC entry 6057 (class 0 OID 0)
-- Dependencies: 332
-- Name: COLUMN secciones.comentario; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.secciones.comentario IS 'Comentario adicional sobre la sección';


--
-- TOC entry 6058 (class 0 OID 0)
-- Dependencies: 332
-- Name: COLUMN secciones.situacion; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.secciones.situacion IS 'Situación de la sección';


--
-- TOC entry 333 (class 1259 OID 159689)
-- Name: secciones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.secciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.secciones_id_seq OWNER TO postgres;

--
-- TOC entry 6059 (class 0 OID 0)
-- Dependencies: 333
-- Name: secciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.secciones_id_seq OWNED BY public.secciones.id;


--
-- TOC entry 334 (class 1259 OID 159690)
-- Name: solicitud; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.solicitud (
    id bigint NOT NULL,
    tipo_solicitud character varying(255) NOT NULL,
    data json NOT NULL,
    fecha timestamp(0) without time zone NOT NULL,
    fechaenvio timestamp(0) without time zone,
    fecharespuesta timestamp(0) without time zone,
    fechaanulacion timestamp(0) without time zone,
    estado character varying(1) NOT NULL,
    nombre text,
    total numeric(8,2),
    moneda character varying(255),
    tipo_documento character varying(1) NOT NULL,
    serie character varying(8) NOT NULL,
    correlativo bigint NOT NULL,
    ticket_solicitud character varying(20),
    persona_id bigint,
    json_persona json,
    sucursal_id bigint NOT NULL,
    json_sucursal json NOT NULL,
    facturador_id bigint NOT NULL,
    json_facturador json NOT NULL,
    movimiento_id bigint,
    solicitud_facturador_id bigint,
    error_code integer,
    error_text text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    public_name character varying(255)
);


ALTER TABLE public.solicitud OWNER TO postgres;

--
-- TOC entry 6060 (class 0 OID 0)
-- Dependencies: 334
-- Name: COLUMN solicitud.json_persona; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.solicitud.json_persona IS 'Datos de cliente';


--
-- TOC entry 6061 (class 0 OID 0)
-- Dependencies: 334
-- Name: COLUMN solicitud.json_sucursal; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.solicitud.json_sucursal IS 'Datos de sucursal relacionados con la solicitud';


--
-- TOC entry 6062 (class 0 OID 0)
-- Dependencies: 334
-- Name: COLUMN solicitud.json_facturador; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.solicitud.json_facturador IS 'Si quiero hacer algo con esta solicitud, voy a tener que usar los datos de facturación que fueron usados para crearla';


--
-- TOC entry 6063 (class 0 OID 0)
-- Dependencies: 334
-- Name: COLUMN solicitud.solicitud_facturador_id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.solicitud.solicitud_facturador_id IS 'Id de solicitud en servidor';


--
-- TOC entry 335 (class 1259 OID 159695)
-- Name: solicitud_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.solicitud_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.solicitud_id_seq OWNER TO postgres;

--
-- TOC entry 6064 (class 0 OID 0)
-- Dependencies: 335
-- Name: solicitud_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.solicitud_id_seq OWNED BY public.solicitud.id;


--
-- TOC entry 336 (class 1259 OID 159696)
-- Name: stockproducto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stockproducto (
    id bigint NOT NULL,
    stockbase numeric(24,6) NOT NULL,
    producto_id bigint NOT NULL,
    unidadbase_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.stockproducto OWNER TO postgres;

--
-- TOC entry 337 (class 1259 OID 159699)
-- Name: stockproducto_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.stockproducto_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.stockproducto_id_seq OWNER TO postgres;

--
-- TOC entry 6065 (class 0 OID 0)
-- Dependencies: 337
-- Name: stockproducto_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.stockproducto_id_seq OWNED BY public.stockproducto.id;


--
-- TOC entry 338 (class 1259 OID 159700)
-- Name: subalmacen; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.subalmacen (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    comentario character varying(255) NOT NULL,
    situacion boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.subalmacen OWNER TO postgres;

--
-- TOC entry 339 (class 1259 OID 159706)
-- Name: subalmacen_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.subalmacen_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.subalmacen_id_seq OWNER TO postgres;

--
-- TOC entry 6066 (class 0 OID 0)
-- Dependencies: 339
-- Name: subalmacen_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.subalmacen_id_seq OWNED BY public.subalmacen.id;


--
-- TOC entry 340 (class 1259 OID 159707)
-- Name: sucursal; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sucursal (
    id bigint NOT NULL,
    razonsocial character varying(255) NOT NULL,
    nombrecomercial character varying(255) NOT NULL,
    direccion character varying(255) NOT NULL,
    ruc character varying(11) NOT NULL,
    email character varying(255) NOT NULL,
    telefonofijo character varying(10) NOT NULL,
    telefonomovil character varying(12) NOT NULL,
    logo text NOT NULL,
    situacion character varying(1) DEFAULT 'E'::character varying NOT NULL,
    facturacion character varying(1) DEFAULT 'N'::character varying NOT NULL,
    tipo character varying(255) DEFAULT 'VENTA'::character varying NOT NULL,
    venta_mayorista character varying(1) DEFAULT 'N'::character varying NOT NULL,
    cantidad_precioventa integer DEFAULT 0 NOT NULL,
    empresa_id bigint NOT NULL,
    ubigeo_id bigint NOT NULL,
    business_intelligence_url text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.sucursal OWNER TO postgres;

--
-- TOC entry 341 (class 1259 OID 159717)
-- Name: sucursal_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sucursal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sucursal_id_seq OWNER TO postgres;

--
-- TOC entry 6067 (class 0 OID 0)
-- Dependencies: 341
-- Name: sucursal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sucursal_id_seq OWNED BY public.sucursal.id;


--
-- TOC entry 342 (class 1259 OID 159718)
-- Name: tablas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tablas (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    comentario character varying(255) NOT NULL,
    tipo character varying(255) DEFAULT 'BASE DE DATOS'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    estado boolean DEFAULT true NOT NULL
);


ALTER TABLE public.tablas OWNER TO postgres;

--
-- TOC entry 343 (class 1259 OID 159725)
-- Name: tablas_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tablas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tablas_id_seq OWNER TO postgres;

--
-- TOC entry 6068 (class 0 OID 0)
-- Dependencies: 343
-- Name: tablas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tablas_id_seq OWNED BY public.tablas.id;


--
-- TOC entry 344 (class 1259 OID 159726)
-- Name: tarjeta; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tarjeta (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    tipo character(1) NOT NULL,
    orden integer NOT NULL,
    situacion boolean DEFAULT true NOT NULL,
    icono character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.tarjeta OWNER TO postgres;

--
-- TOC entry 345 (class 1259 OID 159732)
-- Name: tarjeta_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tarjeta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tarjeta_id_seq OWNER TO postgres;

--
-- TOC entry 6069 (class 0 OID 0)
-- Dependencies: 345
-- Name: tarjeta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tarjeta_id_seq OWNED BY public.tarjeta.id;


--
-- TOC entry 346 (class 1259 OID 159733)
-- Name: tipodocumento; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipodocumento (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    abreviatura character varying(50) NOT NULL,
    stock character varying(255) NOT NULL,
    tipomovimiento_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT tipodocumento_stock_check CHECK (((stock)::text = ANY (ARRAY[('Suma'::character varying)::text, ('Resta'::character varying)::text, ('No'::character varying)::text])))
);


ALTER TABLE public.tipodocumento OWNER TO postgres;

--
-- TOC entry 347 (class 1259 OID 159739)
-- Name: tipodocumento_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipodocumento_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipodocumento_id_seq OWNER TO postgres;

--
-- TOC entry 6070 (class 0 OID 0)
-- Dependencies: 347
-- Name: tipodocumento_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipodocumento_id_seq OWNED BY public.tipodocumento.id;


--
-- TOC entry 348 (class 1259 OID 159740)
-- Name: tipohabitacion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipohabitacion (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    capacidad integer,
    precio numeric(10,2) NOT NULL,
    estado boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    sucursal_id bigint DEFAULT '39'::bigint NOT NULL,
    json_precio_horas json,
    descripcion character varying(255) DEFAULT 'Cama 1.5 Plz + TV + Baño'::character varying,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.tipohabitacion OWNER TO postgres;

--
-- TOC entry 349 (class 1259 OID 159747)
-- Name: tipohabitacion_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipohabitacion_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipohabitacion_id_seq OWNER TO postgres;

--
-- TOC entry 6071 (class 0 OID 0)
-- Dependencies: 349
-- Name: tipohabitacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipohabitacion_id_seq OWNED BY public.tipohabitacion.id;


--
-- TOC entry 350 (class 1259 OID 159748)
-- Name: tipomovimiento; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipomovimiento (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.tipomovimiento OWNER TO postgres;

--
-- TOC entry 351 (class 1259 OID 159751)
-- Name: tipomovimiento_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipomovimiento_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipomovimiento_id_seq OWNER TO postgres;

--
-- TOC entry 6072 (class 0 OID 0)
-- Dependencies: 351
-- Name: tipomovimiento_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipomovimiento_id_seq OWNED BY public.tipomovimiento.id;


--
-- TOC entry 352 (class 1259 OID 159752)
-- Name: tipoopciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipoopciones (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    abreviatura character varying(20) NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    icono text NOT NULL,
    estado boolean NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.tipoopciones OWNER TO postgres;

--
-- TOC entry 353 (class 1259 OID 159758)
-- Name: tipoopciones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipoopciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipoopciones_id_seq OWNER TO postgres;

--
-- TOC entry 6073 (class 0 OID 0)
-- Dependencies: 353
-- Name: tipoopciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipoopciones_id_seq OWNED BY public.tipoopciones.id;


--
-- TOC entry 371 (class 1259 OID 192122)
-- Name: tipopregunta; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipopregunta (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    descripcion character varying(255) NOT NULL,
    situacion character(1) DEFAULT 'E'::bpchar NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.tipopregunta OWNER TO postgres;

--
-- TOC entry 370 (class 1259 OID 192121)
-- Name: tipopregunta_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipopregunta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipopregunta_id_seq OWNER TO postgres;

--
-- TOC entry 6074 (class 0 OID 0)
-- Dependencies: 370
-- Name: tipopregunta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipopregunta_id_seq OWNED BY public.tipopregunta.id;


--
-- TOC entry 354 (class 1259 OID 159759)
-- Name: turno; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.turno (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    abreviatura character varying(255) NOT NULL,
    horainicio time(0) without time zone NOT NULL,
    horafin time(0) without time zone NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.turno OWNER TO postgres;

--
-- TOC entry 355 (class 1259 OID 159764)
-- Name: turno_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.turno_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.turno_id_seq OWNER TO postgres;

--
-- TOC entry 6075 (class 0 OID 0)
-- Dependencies: 355
-- Name: turno_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.turno_id_seq OWNED BY public.turno.id;


--
-- TOC entry 356 (class 1259 OID 159765)
-- Name: ubicacion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ubicacion (
    id bigint NOT NULL,
    codigo character varying(100) NOT NULL,
    descripcion character varying(255) NOT NULL,
    totalfilas integer NOT NULL,
    totalcolumnas integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.ubicacion OWNER TO postgres;

--
-- TOC entry 357 (class 1259 OID 159768)
-- Name: ubicacion_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ubicacion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ubicacion_id_seq OWNER TO postgres;

--
-- TOC entry 6076 (class 0 OID 0)
-- Dependencies: 357
-- Name: ubicacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ubicacion_id_seq OWNED BY public.ubicacion.id;


--
-- TOC entry 358 (class 1259 OID 159769)
-- Name: ubigeo; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ubigeo (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    codigo character varying(6) NOT NULL,
    inei character varying(6) NOT NULL,
    tipo character varying(1) NOT NULL,
    ubigeo_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.ubigeo OWNER TO postgres;

--
-- TOC entry 359 (class 1259 OID 159772)
-- Name: ubigeos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ubigeos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ubigeos_id_seq OWNER TO postgres;

--
-- TOC entry 6077 (class 0 OID 0)
-- Dependencies: 359
-- Name: ubigeos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ubigeos_id_seq OWNED BY public.ubigeo.id;


--
-- TOC entry 360 (class 1259 OID 159773)
-- Name: unidad; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.unidad (
    id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    abreviatura character varying(255) NOT NULL,
    tipo character varying(100) NOT NULL,
    sunat boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.unidad OWNER TO postgres;

--
-- TOC entry 361 (class 1259 OID 159779)
-- Name: unidad_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.unidad_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.unidad_id_seq OWNER TO postgres;

--
-- TOC entry 6078 (class 0 OID 0)
-- Dependencies: 361
-- Name: unidad_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.unidad_id_seq OWNED BY public.unidad.id;


--
-- TOC entry 362 (class 1259 OID 159780)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    persona_id bigint,
    perfil_id bigint
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 363 (class 1259 OID 159785)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- TOC entry 6079 (class 0 OID 0)
-- Dependencies: 363
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 379 (class 1259 OID 192210)
-- Name: valordet_respuesta; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.valordet_respuesta (
    id bigint NOT NULL,
    valor text NOT NULL,
    det_respuesta_id bigint NOT NULL,
    sucursal_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.valordet_respuesta OWNER TO postgres;

--
-- TOC entry 378 (class 1259 OID 192209)
-- Name: valordet_respuesta_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.valordet_respuesta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.valordet_respuesta_id_seq OWNER TO postgres;

--
-- TOC entry 6080 (class 0 OID 0)
-- Dependencies: 378
-- Name: valordet_respuesta_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.valordet_respuesta_id_seq OWNED BY public.valordet_respuesta.id;


--
-- TOC entry 5115 (class 2604 OID 159786)
-- Name: banco id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.banco ALTER COLUMN id SET DEFAULT nextval('public.banco_id_seq'::regclass);


--
-- TOC entry 5117 (class 2604 OID 159787)
-- Name: billeteradigital id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billeteradigital ALTER COLUMN id SET DEFAULT nextval('public.billeteradigital_id_seq'::regclass);


--
-- TOC entry 5119 (class 2604 OID 159788)
-- Name: caja id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.caja ALTER COLUMN id SET DEFAULT nextval('public.caja_id_seq'::regclass);


--
-- TOC entry 5122 (class 2604 OID 159789)
-- Name: campos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.campos ALTER COLUMN id SET DEFAULT nextval('public.campos_id_seq'::regclass);


--
-- TOC entry 5127 (class 2604 OID 159790)
-- Name: categoria id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoria ALTER COLUMN id SET DEFAULT nextval('public.categoria_id_seq'::regclass);


--
-- TOC entry 5131 (class 2604 OID 159791)
-- Name: categoriaparametros id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoriaparametros ALTER COLUMN id SET DEFAULT nextval('public.categoriaparametros_id_seq'::regclass);


--
-- TOC entry 5295 (class 2604 OID 192078)
-- Name: categoriapregunta id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoriapregunta ALTER COLUMN id SET DEFAULT nextval('public.categoriapregunta_id_seq'::regclass);


--
-- TOC entry 5133 (class 2604 OID 159792)
-- Name: conceptopago id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conceptopago ALTER COLUMN id SET DEFAULT nextval('public.conceptopago_id_seq'::regclass);


--
-- TOC entry 5304 (class 2604 OID 192135)
-- Name: det_encuesta id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_encuesta ALTER COLUMN id SET DEFAULT nextval('public.det_encuesta_id_seq'::regclass);


--
-- TOC entry 5135 (class 2604 OID 159793)
-- Name: det_movalmacen id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movalmacen ALTER COLUMN id SET DEFAULT nextval('public.det_movalmacen_id_seq'::regclass);


--
-- TOC entry 5137 (class 2604 OID 159794)
-- Name: det_movcaja id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movcaja ALTER COLUMN id SET DEFAULT nextval('public.det_movcaja_id_seq'::regclass);


--
-- TOC entry 5139 (class 2604 OID 159795)
-- Name: det_movhabitacion id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movhabitacion ALTER COLUMN id SET DEFAULT nextval('public.det_movhabitacion_id_seq'::regclass);


--
-- TOC entry 5143 (class 2604 OID 159796)
-- Name: det_movventa id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movventa ALTER COLUMN id SET DEFAULT nextval('public.det_movventa_id_seq'::regclass);


--
-- TOC entry 5309 (class 2604 OID 192191)
-- Name: det_respuesta id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_respuesta ALTER COLUMN id SET DEFAULT nextval('public.det_respuesta_id_seq'::regclass);


--
-- TOC entry 5147 (class 2604 OID 159797)
-- Name: distribuidor id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.distribuidor ALTER COLUMN id SET DEFAULT nextval('public.distribuidor_id_seq'::regclass);


--
-- TOC entry 5148 (class 2604 OID 159798)
-- Name: empresas id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empresas ALTER COLUMN id SET DEFAULT nextval('public.empresas_id_seq'::regclass);


--
-- TOC entry 5297 (class 2604 OID 192107)
-- Name: encuesta id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.encuesta ALTER COLUMN id SET DEFAULT nextval('public.encuesta_id_seq'::regclass);


--
-- TOC entry 5149 (class 2604 OID 159799)
-- Name: facturador id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.facturador ALTER COLUMN id SET DEFAULT nextval('public.facturador_id_seq'::regclass);


--
-- TOC entry 5153 (class 2604 OID 159800)
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- TOC entry 5155 (class 2604 OID 159801)
-- Name: habitacion id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.habitacion ALTER COLUMN id SET DEFAULT nextval('public.habitacion_id_seq'::regclass);


--
-- TOC entry 5292 (class 2604 OID 183887)
-- Name: historial_habitacion id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historial_habitacion ALTER COLUMN id SET DEFAULT nextval('public.historial_habitacion_id_seq'::regclass);


--
-- TOC entry 5157 (class 2604 OID 159802)
-- Name: huesped id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.huesped ALTER COLUMN id SET DEFAULT nextval('public.huesped_id_seq'::regclass);


--
-- TOC entry 5158 (class 2604 OID 159803)
-- Name: igv id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.igv ALTER COLUMN id SET DEFAULT nextval('public.igv_id_seq'::regclass);


--
-- TOC entry 5160 (class 2604 OID 159804)
-- Name: kardex id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kardex ALTER COLUMN id SET DEFAULT nextval('public.kardex_id_seq'::regclass);


--
-- TOC entry 5165 (class 2604 OID 159805)
-- Name: listaunidad id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.listaunidad ALTER COLUMN id SET DEFAULT nextval('public.listaunidad_id_seq'::regclass);


--
-- TOC entry 5166 (class 2604 OID 159806)
-- Name: marca id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.marca ALTER COLUMN id SET DEFAULT nextval('public.marca_id_seq'::regclass);


--
-- TOC entry 5167 (class 2604 OID 159807)
-- Name: mediopago id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mediopago ALTER COLUMN id SET DEFAULT nextval('public.mediopago_id_seq'::regclass);


--
-- TOC entry 5169 (class 2604 OID 159808)
-- Name: menuopciones id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.menuopciones ALTER COLUMN id SET DEFAULT nextval('public.menuopciones_id_seq'::regclass);


--
-- TOC entry 5174 (class 2604 OID 159809)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 5175 (class 2604 OID 159810)
-- Name: modulos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.modulos ALTER COLUMN id SET DEFAULT nextval('public.modulos_id_seq'::regclass);


--
-- TOC entry 5177 (class 2604 OID 159811)
-- Name: motivomovimiento id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.motivomovimiento ALTER COLUMN id SET DEFAULT nextval('public.motivomovimiento_id_seq'::regclass);


--
-- TOC entry 5179 (class 2604 OID 159812)
-- Name: movalmacen id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movalmacen ALTER COLUMN id SET DEFAULT nextval('public.movalmacen_id_seq'::regclass);


--
-- TOC entry 5181 (class 2604 OID 159813)
-- Name: movcaja id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movcaja ALTER COLUMN id SET DEFAULT nextval('public.movcaja_id_seq'::regclass);


--
-- TOC entry 5183 (class 2604 OID 159814)
-- Name: movhabitacion id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movhabitacion ALTER COLUMN id SET DEFAULT nextval('public.movhabitacion_id_seq'::regclass);


--
-- TOC entry 5186 (class 2604 OID 159815)
-- Name: movimiento id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimiento ALTER COLUMN id SET DEFAULT nextval('public.movimiento_id_seq'::regclass);


--
-- TOC entry 5188 (class 2604 OID 159816)
-- Name: movventa id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movventa ALTER COLUMN id SET DEFAULT nextval('public.movventa_id_seq'::regclass);


--
-- TOC entry 5195 (class 2604 OID 159817)
-- Name: operaciones id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operaciones ALTER COLUMN id SET DEFAULT nextval('public.operaciones_id_seq'::regclass);


--
-- TOC entry 5199 (class 2604 OID 159818)
-- Name: parametros id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parametros ALTER COLUMN id SET DEFAULT nextval('public.parametros_id_seq'::regclass);


--
-- TOC entry 5203 (class 2604 OID 159819)
-- Name: pasarelapago id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pasarelapago ALTER COLUMN id SET DEFAULT nextval('public.pasarelapago_id_seq'::regclass);


--
-- TOC entry 5205 (class 2604 OID 159820)
-- Name: perfiles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.perfiles ALTER COLUMN id SET DEFAULT nextval('public.perfiles_id_seq'::regclass);


--
-- TOC entry 5206 (class 2604 OID 159821)
-- Name: perfilsucursal id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.perfilsucursal ALTER COLUMN id SET DEFAULT nextval('public.perfilsucursal_id_seq'::regclass);


--
-- TOC entry 5207 (class 2604 OID 159822)
-- Name: permisousuario id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permisousuario ALTER COLUMN id SET DEFAULT nextval('public.permisousuario_id_seq'::regclass);


--
-- TOC entry 5209 (class 2604 OID 159823)
-- Name: persona id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.persona ALTER COLUMN id SET DEFAULT nextval('public.persona_id_seq'::regclass);


--
-- TOC entry 5211 (class 2604 OID 159824)
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- TOC entry 5212 (class 2604 OID 159825)
-- Name: personamaestro id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personamaestro ALTER COLUMN id SET DEFAULT nextval('public.personamaestro_id_seq'::regclass);


--
-- TOC entry 5214 (class 2604 OID 159826)
-- Name: piso id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.piso ALTER COLUMN id SET DEFAULT nextval('public.piso_id_seq'::regclass);


--
-- TOC entry 5217 (class 2604 OID 159827)
-- Name: producto id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto ALTER COLUMN id SET DEFAULT nextval('public.producto_id_seq'::regclass);


--
-- TOC entry 5228 (class 2604 OID 159828)
-- Name: rel_camposucursal id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_camposucursal ALTER COLUMN id SET DEFAULT nextval('public.rel_camposucursal_id_seq'::regclass);


--
-- TOC entry 5232 (class 2604 OID 159829)
-- Name: rel_categoriasucursal id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_categoriasucursal ALTER COLUMN id SET DEFAULT nextval('public.rel_categoriasucursal_id_seq'::regclass);


--
-- TOC entry 5234 (class 2604 OID 159830)
-- Name: rel_listaunidadsucursal id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_listaunidadsucursal ALTER COLUMN id SET DEFAULT nextval('public.rel_listaunidadsucursal_id_seq'::regclass);


--
-- TOC entry 5236 (class 2604 OID 159831)
-- Name: rel_mediopagopasarela id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_mediopagopasarela ALTER COLUMN id SET DEFAULT nextval('public.rel_mediopagopasarela_id_seq'::regclass);


--
-- TOC entry 5237 (class 2604 OID 159832)
-- Name: rel_operacionsucursal id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_operacionsucursal ALTER COLUMN id SET DEFAULT nextval('public.rel_operacionsucursal_id_seq'::regclass);


--
-- TOC entry 5240 (class 2604 OID 159833)
-- Name: rel_operacionsucursalperfil id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_operacionsucursalperfil ALTER COLUMN id SET DEFAULT nextval('public.rel_operacionsucursalperfil_id_seq'::regclass);


--
-- TOC entry 5242 (class 2604 OID 159834)
-- Name: rel_parametroempresa id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_parametroempresa ALTER COLUMN id SET DEFAULT nextval('public.rel_parametroempresa_id_seq'::regclass);


--
-- TOC entry 5243 (class 2604 OID 159835)
-- Name: rel_parametrosucursal id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_parametrosucursal ALTER COLUMN id SET DEFAULT nextval('public.rel_parametrosucursal_id_seq'::regclass);


--
-- TOC entry 5244 (class 2604 OID 159836)
-- Name: rel_productosucursal id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_productosucursal ALTER COLUMN id SET DEFAULT nextval('public.rel_productosucursal_id_seq'::regclass);


--
-- TOC entry 5311 (class 2604 OID 200294)
-- Name: rel_seccionopcionmenu id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionopcionmenu ALTER COLUMN id SET DEFAULT nextval('public.rel_seccionopcionmenu_id_seq'::regclass);


--
-- TOC entry 5251 (class 2604 OID 159837)
-- Name: rel_seccionsucursal id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionsucursal ALTER COLUMN id SET DEFAULT nextval('public.rel_seccionsucursal_id_seq'::regclass);


--
-- TOC entry 5253 (class 2604 OID 159838)
-- Name: rel_seccionsucursalperfil id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionsucursalperfil ALTER COLUMN id SET DEFAULT nextval('public.rel_seccionsucursalperfil_id_seq'::regclass);


--
-- TOC entry 5254 (class 2604 OID 159839)
-- Name: rel_tablasucursal id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_tablasucursal ALTER COLUMN id SET DEFAULT nextval('public.rel_tablasucursal_id_seq'::regclass);


--
-- TOC entry 5255 (class 2604 OID 159840)
-- Name: rel_turnocaja id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_turnocaja ALTER COLUMN id SET DEFAULT nextval('public.rel_turnocaja_id_seq'::regclass);


--
-- TOC entry 5257 (class 2604 OID 159841)
-- Name: reserva id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reserva ALTER COLUMN id SET DEFAULT nextval('public.reserva_id_seq'::regclass);


--
-- TOC entry 5308 (class 2604 OID 192167)
-- Name: respuesta id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.respuesta ALTER COLUMN id SET DEFAULT nextval('public.respuesta_id_seq'::regclass);


--
-- TOC entry 5259 (class 2604 OID 159842)
-- Name: rol id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol ALTER COLUMN id SET DEFAULT nextval('public.rol_id_seq'::regclass);


--
-- TOC entry 5260 (class 2604 OID 159843)
-- Name: secciones id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secciones ALTER COLUMN id SET DEFAULT nextval('public.secciones_id_seq'::regclass);


--
-- TOC entry 5263 (class 2604 OID 159844)
-- Name: solicitud id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitud ALTER COLUMN id SET DEFAULT nextval('public.solicitud_id_seq'::regclass);


--
-- TOC entry 5264 (class 2604 OID 159845)
-- Name: stockproducto id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stockproducto ALTER COLUMN id SET DEFAULT nextval('public.stockproducto_id_seq'::regclass);


--
-- TOC entry 5265 (class 2604 OID 159846)
-- Name: subalmacen id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subalmacen ALTER COLUMN id SET DEFAULT nextval('public.subalmacen_id_seq'::regclass);


--
-- TOC entry 5267 (class 2604 OID 159847)
-- Name: sucursal id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sucursal ALTER COLUMN id SET DEFAULT nextval('public.sucursal_id_seq'::regclass);


--
-- TOC entry 5273 (class 2604 OID 159848)
-- Name: tablas id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tablas ALTER COLUMN id SET DEFAULT nextval('public.tablas_id_seq'::regclass);


--
-- TOC entry 5276 (class 2604 OID 159849)
-- Name: tarjeta id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tarjeta ALTER COLUMN id SET DEFAULT nextval('public.tarjeta_id_seq'::regclass);


--
-- TOC entry 5278 (class 2604 OID 159850)
-- Name: tipodocumento id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipodocumento ALTER COLUMN id SET DEFAULT nextval('public.tipodocumento_id_seq'::regclass);


--
-- TOC entry 5279 (class 2604 OID 159851)
-- Name: tipohabitacion id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipohabitacion ALTER COLUMN id SET DEFAULT nextval('public.tipohabitacion_id_seq'::regclass);


--
-- TOC entry 5283 (class 2604 OID 159852)
-- Name: tipomovimiento id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipomovimiento ALTER COLUMN id SET DEFAULT nextval('public.tipomovimiento_id_seq'::regclass);


--
-- TOC entry 5284 (class 2604 OID 159853)
-- Name: tipoopciones id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipoopciones ALTER COLUMN id SET DEFAULT nextval('public.tipoopciones_id_seq'::regclass);


--
-- TOC entry 5302 (class 2604 OID 192125)
-- Name: tipopregunta id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipopregunta ALTER COLUMN id SET DEFAULT nextval('public.tipopregunta_id_seq'::regclass);


--
-- TOC entry 5286 (class 2604 OID 159854)
-- Name: turno id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.turno ALTER COLUMN id SET DEFAULT nextval('public.turno_id_seq'::regclass);


--
-- TOC entry 5287 (class 2604 OID 159855)
-- Name: ubicacion id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubicacion ALTER COLUMN id SET DEFAULT nextval('public.ubicacion_id_seq'::regclass);


--
-- TOC entry 5288 (class 2604 OID 159856)
-- Name: ubigeo id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubigeo ALTER COLUMN id SET DEFAULT nextval('public.ubigeos_id_seq'::regclass);


--
-- TOC entry 5289 (class 2604 OID 159857)
-- Name: unidad id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.unidad ALTER COLUMN id SET DEFAULT nextval('public.unidad_id_seq'::regclass);


--
-- TOC entry 5291 (class 2604 OID 159858)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 5310 (class 2604 OID 192213)
-- Name: valordet_respuesta id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.valordet_respuesta ALTER COLUMN id SET DEFAULT nextval('public.valordet_respuesta_id_seq'::regclass);


--
-- TOC entry 5791 (class 0 OID 159304)
-- Dependencies: 216


--
-- TOC entry 5793 (class 0 OID 159309)
-- Dependencies: 218


--
-- TOC entry 5795 (class 0 OID 159314)
-- Dependencies: 220


--
-- TOC entry 5797 (class 0 OID 159320)
-- Dependencies: 222


--
-- TOC entry 5799 (class 0 OID 159330)
-- Dependencies: 224


--
-- TOC entry 5801 (class 0 OID 159339)
-- Dependencies: 226


--
-- TOC entry 5942 (class 0 OID 192075)
-- Dependencies: 367


--
-- TOC entry 5803 (class 0 OID 159344)
-- Dependencies: 228


--
-- TOC entry 5948 (class 0 OID 192132)
-- Dependencies: 373


--
-- TOC entry 5805 (class 0 OID 159349)
-- Dependencies: 230


--
-- TOC entry 5807 (class 0 OID 159356)
-- Dependencies: 232


--
-- TOC entry 5809 (class 0 OID 159363)
-- Dependencies: 234


--
-- TOC entry 5811 (class 0 OID 159372)
-- Dependencies: 236


--
-- TOC entry 5952 (class 0 OID 192188)
-- Dependencies: 377


--
-- TOC entry 5813 (class 0 OID 159381)
-- Dependencies: 238


--
-- TOC entry 5815 (class 0 OID 159385)
-- Dependencies: 240


--
-- TOC entry 5944 (class 0 OID 192104)
-- Dependencies: 369


--
-- TOC entry 5817 (class 0 OID 159391)
-- Dependencies: 242


--
-- TOC entry 5819 (class 0 OID 159400)
-- Dependencies: 244


--
-- TOC entry 5821 (class 0 OID 159407)
-- Dependencies: 246


--
-- TOC entry 5940 (class 0 OID 183884)
-- Dependencies: 365


--
-- TOC entry 5823 (class 0 OID 159414)
-- Dependencies: 248


--
-- TOC entry 5825 (class 0 OID 159418)
-- Dependencies: 250


--
-- TOC entry 5827 (class 0 OID 159425)
-- Dependencies: 252


--
-- TOC entry 5829 (class 0 OID 159435)
-- Dependencies: 254


--
-- TOC entry 5831 (class 0 OID 159439)
-- Dependencies: 256


--
-- TOC entry 5833 (class 0 OID 159445)
-- Dependencies: 258


--
-- TOC entry 5835 (class 0 OID 159450)
-- Dependencies: 260


--
-- TOC entry 5837 (class 0 OID 159460)
-- Dependencies: 262


--
-- TOC entry 5839 (class 0 OID 159464)
-- Dependencies: 264


--
-- TOC entry 5841 (class 0 OID 159471)
-- Dependencies: 266


--
-- TOC entry 5843 (class 0 OID 159478)
-- Dependencies: 268


--
-- TOC entry 5845 (class 0 OID 159483)
-- Dependencies: 270


--
-- TOC entry 5847 (class 0 OID 159490)
-- Dependencies: 272


--
-- TOC entry 5849 (class 0 OID 159498)
-- Dependencies: 274


--
-- TOC entry 5851 (class 0 OID 159505)
-- Dependencies: 276


--
-- TOC entry 5853 (class 0 OID 159517)
-- Dependencies: 278


--
-- TOC entry 5855 (class 0 OID 159526)
-- Dependencies: 280


--
-- TOC entry 5857 (class 0 OID 159535)
-- Dependencies: 282


--
-- TOC entry 5859 (class 0 OID 159540)
-- Dependencies: 284


--
-- TOC entry 5860 (class 0 OID 159545)
-- Dependencies: 285


--
-- TOC entry 5862 (class 0 OID 159551)
-- Dependencies: 287


--
-- TOC entry 5864 (class 0 OID 159555)
-- Dependencies: 289


--
-- TOC entry 5866 (class 0 OID 159560)
-- Dependencies: 291


--
-- TOC entry 5868 (class 0 OID 159567)
-- Dependencies: 293


--
-- TOC entry 5870 (class 0 OID 159573)
-- Dependencies: 295


--
-- TOC entry 5872 (class 0 OID 159580)
-- Dependencies: 297


--
-- TOC entry 5874 (class 0 OID 159586)
-- Dependencies: 299


--
-- TOC entry 5876 (class 0 OID 159602)
-- Dependencies: 301


--
-- TOC entry 5878 (class 0 OID 159609)
-- Dependencies: 303


--
-- TOC entry 5880 (class 0 OID 159614)
-- Dependencies: 305


--
-- TOC entry 5882 (class 0 OID 159619)
-- Dependencies: 307


--
-- TOC entry 5884 (class 0 OID 159623)
-- Dependencies: 309


--
-- TOC entry 5886 (class 0 OID 159629)
-- Dependencies: 311


--
-- TOC entry 5888 (class 0 OID 159634)
-- Dependencies: 313


--
-- TOC entry 5890 (class 0 OID 159638)
-- Dependencies: 315


--
-- TOC entry 5892 (class 0 OID 159642)
-- Dependencies: 317


--
-- TOC entry 5956 (class 0 OID 200291)
-- Dependencies: 381


--
-- TOC entry 5894 (class 0 OID 159652)
-- Dependencies: 319


--
-- TOC entry 5896 (class 0 OID 159657)
-- Dependencies: 321


--
-- TOC entry 5898 (class 0 OID 159661)
-- Dependencies: 323


--
-- TOC entry 5900 (class 0 OID 159665)
-- Dependencies: 325


--
-- TOC entry 5902 (class 0 OID 159670)
-- Dependencies: 327


--
-- TOC entry 5950 (class 0 OID 192164)
-- Dependencies: 375


--
-- TOC entry 5904 (class 0 OID 159675)
-- Dependencies: 329


--
-- TOC entry 5906 (class 0 OID 159679)
-- Dependencies: 331


--
-- TOC entry 5907 (class 0 OID 159682)
-- Dependencies: 332


--
-- TOC entry 5909 (class 0 OID 159690)
-- Dependencies: 334


--
-- TOC entry 5911 (class 0 OID 159696)
-- Dependencies: 336


--
-- TOC entry 5913 (class 0 OID 159700)
-- Dependencies: 338


--
-- TOC entry 5915 (class 0 OID 159707)
-- Dependencies: 340


--
-- TOC entry 5917 (class 0 OID 159718)
-- Dependencies: 342


--
-- TOC entry 5919 (class 0 OID 159726)
-- Dependencies: 344


--
-- TOC entry 5921 (class 0 OID 159733)
-- Dependencies: 346


--
-- TOC entry 5923 (class 0 OID 159740)
-- Dependencies: 348


--
-- TOC entry 5925 (class 0 OID 159748)
-- Dependencies: 350


--
-- TOC entry 5927 (class 0 OID 159752)
-- Dependencies: 352


--
-- TOC entry 5946 (class 0 OID 192122)
-- Dependencies: 371


--
-- TOC entry 5929 (class 0 OID 159759)
-- Dependencies: 354


--
-- TOC entry 5931 (class 0 OID 159765)
-- Dependencies: 356


--
-- TOC entry 5933 (class 0 OID 159769)
-- Dependencies: 358


--
-- TOC entry 5935 (class 0 OID 159773)
-- Dependencies: 360


--
-- TOC entry 5937 (class 0 OID 159780)
-- Dependencies: 362


--
-- TOC entry 5954 (class 0 OID 192210)
-- Dependencies: 379


--
-- TOC entry 6081 (class 0 OID 0)
-- Dependencies: 217
-- Name: banco_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6082 (class 0 OID 0)
-- Dependencies: 219
-- Name: billeteradigital_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6083 (class 0 OID 0)
-- Dependencies: 221
-- Name: caja_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6084 (class 0 OID 0)
-- Dependencies: 223
-- Name: campos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6085 (class 0 OID 0)
-- Dependencies: 225
-- Name: categoria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6086 (class 0 OID 0)
-- Dependencies: 227
-- Name: categoriaparametros_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6087 (class 0 OID 0)
-- Dependencies: 366
-- Name: categoriapregunta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6088 (class 0 OID 0)
-- Dependencies: 229
-- Name: conceptopago_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6089 (class 0 OID 0)
-- Dependencies: 372
-- Name: det_encuesta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6090 (class 0 OID 0)
-- Dependencies: 231
-- Name: det_movalmacen_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6091 (class 0 OID 0)
-- Dependencies: 233
-- Name: det_movcaja_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6092 (class 0 OID 0)
-- Dependencies: 235
-- Name: det_movhabitacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6093 (class 0 OID 0)
-- Dependencies: 237
-- Name: det_movventa_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6094 (class 0 OID 0)
-- Dependencies: 376
-- Name: det_respuesta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6095 (class 0 OID 0)
-- Dependencies: 239
-- Name: distribuidor_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6096 (class 0 OID 0)
-- Dependencies: 241
-- Name: empresas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6097 (class 0 OID 0)
-- Dependencies: 368
-- Name: encuesta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6098 (class 0 OID 0)
-- Dependencies: 243
-- Name: facturador_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6099 (class 0 OID 0)
-- Dependencies: 245
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6100 (class 0 OID 0)
-- Dependencies: 247
-- Name: habitacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6101 (class 0 OID 0)
-- Dependencies: 364
-- Name: historial_habitacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6102 (class 0 OID 0)
-- Dependencies: 249
-- Name: huesped_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6103 (class 0 OID 0)
-- Dependencies: 251
-- Name: igv_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6104 (class 0 OID 0)
-- Dependencies: 253
-- Name: kardex_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6105 (class 0 OID 0)
-- Dependencies: 255
-- Name: listaunidad_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6106 (class 0 OID 0)
-- Dependencies: 257
-- Name: marca_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6107 (class 0 OID 0)
-- Dependencies: 259
-- Name: mediopago_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6108 (class 0 OID 0)
-- Dependencies: 261
-- Name: menuopciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6109 (class 0 OID 0)
-- Dependencies: 263
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6110 (class 0 OID 0)
-- Dependencies: 265
-- Name: modulos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6111 (class 0 OID 0)
-- Dependencies: 267
-- Name: motivomovimiento_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6112 (class 0 OID 0)
-- Dependencies: 269
-- Name: movalmacen_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6113 (class 0 OID 0)
-- Dependencies: 271
-- Name: movcaja_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6114 (class 0 OID 0)
-- Dependencies: 273
-- Name: movhabitacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6115 (class 0 OID 0)
-- Dependencies: 275
-- Name: movimiento_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6116 (class 0 OID 0)
-- Dependencies: 277
-- Name: movventa_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6117 (class 0 OID 0)
-- Dependencies: 279
-- Name: operaciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6118 (class 0 OID 0)
-- Dependencies: 281
-- Name: parametros_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6119 (class 0 OID 0)
-- Dependencies: 283
-- Name: pasarelapago_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6120 (class 0 OID 0)
-- Dependencies: 286
-- Name: perfiles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6121 (class 0 OID 0)
-- Dependencies: 288
-- Name: perfilsucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6122 (class 0 OID 0)
-- Dependencies: 290
-- Name: permisousuario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6123 (class 0 OID 0)
-- Dependencies: 292
-- Name: persona_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6124 (class 0 OID 0)
-- Dependencies: 294
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6125 (class 0 OID 0)
-- Dependencies: 296
-- Name: personamaestro_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6126 (class 0 OID 0)
-- Dependencies: 298
-- Name: piso_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6127 (class 0 OID 0)
-- Dependencies: 300
-- Name: producto_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6128 (class 0 OID 0)
-- Dependencies: 302
-- Name: rel_camposucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6129 (class 0 OID 0)
-- Dependencies: 304
-- Name: rel_categoriasucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6130 (class 0 OID 0)
-- Dependencies: 306
-- Name: rel_listaunidadsucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6131 (class 0 OID 0)
-- Dependencies: 308
-- Name: rel_mediopagopasarela_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6132 (class 0 OID 0)
-- Dependencies: 310
-- Name: rel_operacionsucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6133 (class 0 OID 0)
-- Dependencies: 312
-- Name: rel_operacionsucursalperfil_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6134 (class 0 OID 0)
-- Dependencies: 314
-- Name: rel_parametroempresa_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6135 (class 0 OID 0)
-- Dependencies: 316
-- Name: rel_parametrosucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6136 (class 0 OID 0)
-- Dependencies: 318
-- Name: rel_productosucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6137 (class 0 OID 0)
-- Dependencies: 380
-- Name: rel_seccionopcionmenu_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6138 (class 0 OID 0)
-- Dependencies: 320
-- Name: rel_seccionsucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6139 (class 0 OID 0)
-- Dependencies: 322
-- Name: rel_seccionsucursalperfil_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6140 (class 0 OID 0)
-- Dependencies: 324
-- Name: rel_tablasucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6141 (class 0 OID 0)
-- Dependencies: 326
-- Name: rel_turnocaja_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6142 (class 0 OID 0)
-- Dependencies: 328
-- Name: reserva_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6143 (class 0 OID 0)
-- Dependencies: 374
-- Name: respuesta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6144 (class 0 OID 0)
-- Dependencies: 330
-- Name: rol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6145 (class 0 OID 0)
-- Dependencies: 333
-- Name: secciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6146 (class 0 OID 0)
-- Dependencies: 335
-- Name: solicitud_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6147 (class 0 OID 0)
-- Dependencies: 337
-- Name: stockproducto_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6148 (class 0 OID 0)
-- Dependencies: 339
-- Name: subalmacen_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6149 (class 0 OID 0)
-- Dependencies: 341
-- Name: sucursal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6150 (class 0 OID 0)
-- Dependencies: 343
-- Name: tablas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6151 (class 0 OID 0)
-- Dependencies: 345
-- Name: tarjeta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6152 (class 0 OID 0)
-- Dependencies: 347
-- Name: tipodocumento_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6153 (class 0 OID 0)
-- Dependencies: 349
-- Name: tipohabitacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6154 (class 0 OID 0)
-- Dependencies: 351
-- Name: tipomovimiento_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6155 (class 0 OID 0)
-- Dependencies: 353
-- Name: tipoopciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6156 (class 0 OID 0)
-- Dependencies: 370
-- Name: tipopregunta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6157 (class 0 OID 0)
-- Dependencies: 355
-- Name: turno_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6158 (class 0 OID 0)
-- Dependencies: 357
-- Name: ubicacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6159 (class 0 OID 0)
-- Dependencies: 359
-- Name: ubigeos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6160 (class 0 OID 0)
-- Dependencies: 361
-- Name: unidad_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6161 (class 0 OID 0)
-- Dependencies: 363
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 6162 (class 0 OID 0)
-- Dependencies: 378
-- Name: valordet_respuesta_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--



--
-- TOC entry 5314 (class 2606 OID 159860)
-- Name: banco banco_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.banco
    ADD CONSTRAINT banco_pkey PRIMARY KEY (id);


--
-- TOC entry 5316 (class 2606 OID 159862)
-- Name: billeteradigital billeteradigital_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.billeteradigital
    ADD CONSTRAINT billeteradigital_pkey PRIMARY KEY (id);


--
-- TOC entry 5318 (class 2606 OID 159864)
-- Name: caja caja_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.caja
    ADD CONSTRAINT caja_pkey PRIMARY KEY (id);


--
-- TOC entry 5320 (class 2606 OID 159866)
-- Name: campos campos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.campos
    ADD CONSTRAINT campos_pkey PRIMARY KEY (id);


--
-- TOC entry 5322 (class 2606 OID 159868)
-- Name: categoria categoria_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoria
    ADD CONSTRAINT categoria_pkey PRIMARY KEY (id);


--
-- TOC entry 5324 (class 2606 OID 159870)
-- Name: categoriaparametros categoriaparametros_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoriaparametros
    ADD CONSTRAINT categoriaparametros_pkey PRIMARY KEY (id);


--
-- TOC entry 5475 (class 2606 OID 192081)
-- Name: categoriapregunta categoriapregunta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoriapregunta
    ADD CONSTRAINT categoriapregunta_pkey PRIMARY KEY (id);


--
-- TOC entry 5326 (class 2606 OID 159872)
-- Name: conceptopago conceptopago_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conceptopago
    ADD CONSTRAINT conceptopago_pkey PRIMARY KEY (id);


--
-- TOC entry 5481 (class 2606 OID 192142)
-- Name: det_encuesta det_encuesta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_encuesta
    ADD CONSTRAINT det_encuesta_pkey PRIMARY KEY (id);


--
-- TOC entry 5328 (class 2606 OID 159874)
-- Name: det_movalmacen det_movalmacen_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movalmacen
    ADD CONSTRAINT det_movalmacen_pkey PRIMARY KEY (id);


--
-- TOC entry 5330 (class 2606 OID 159876)
-- Name: det_movcaja det_movcaja_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movcaja
    ADD CONSTRAINT det_movcaja_pkey PRIMARY KEY (id);


--
-- TOC entry 5332 (class 2606 OID 159878)
-- Name: det_movhabitacion det_movhabitacion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movhabitacion
    ADD CONSTRAINT det_movhabitacion_pkey PRIMARY KEY (id);


--
-- TOC entry 5334 (class 2606 OID 159880)
-- Name: det_movventa det_movventa_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movventa
    ADD CONSTRAINT det_movventa_pkey PRIMARY KEY (id);


--
-- TOC entry 5485 (class 2606 OID 192193)
-- Name: det_respuesta det_respuesta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_respuesta
    ADD CONSTRAINT det_respuesta_pkey PRIMARY KEY (id);


--
-- TOC entry 5336 (class 2606 OID 159882)
-- Name: distribuidor distribuidor_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.distribuidor
    ADD CONSTRAINT distribuidor_pkey PRIMARY KEY (id);


--
-- TOC entry 5338 (class 2606 OID 159884)
-- Name: empresas empresas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empresas
    ADD CONSTRAINT empresas_pkey PRIMARY KEY (id);


--
-- TOC entry 5340 (class 2606 OID 159886)
-- Name: empresas empresas_ruc_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.empresas
    ADD CONSTRAINT empresas_ruc_unique UNIQUE (ruc);


--
-- TOC entry 5477 (class 2606 OID 192115)
-- Name: encuesta encuesta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.encuesta
    ADD CONSTRAINT encuesta_pkey PRIMARY KEY (id);


--
-- TOC entry 5342 (class 2606 OID 159888)
-- Name: facturador facturador_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.facturador
    ADD CONSTRAINT facturador_pkey PRIMARY KEY (id);


--
-- TOC entry 5344 (class 2606 OID 159890)
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 5346 (class 2606 OID 159892)
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- TOC entry 5348 (class 2606 OID 159894)
-- Name: habitacion habitacion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.habitacion
    ADD CONSTRAINT habitacion_pkey PRIMARY KEY (id);


--
-- TOC entry 5473 (class 2606 OID 183891)
-- Name: historial_habitacion historial_habitacion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historial_habitacion
    ADD CONSTRAINT historial_habitacion_pkey PRIMARY KEY (id);


--
-- TOC entry 5350 (class 2606 OID 159896)
-- Name: huesped huesped_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.huesped
    ADD CONSTRAINT huesped_pkey PRIMARY KEY (id);


--
-- TOC entry 5352 (class 2606 OID 159898)
-- Name: igv igv_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.igv
    ADD CONSTRAINT igv_pkey PRIMARY KEY (id);


--
-- TOC entry 5354 (class 2606 OID 159900)
-- Name: kardex kardex_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kardex
    ADD CONSTRAINT kardex_pkey PRIMARY KEY (id);


--
-- TOC entry 5356 (class 2606 OID 159902)
-- Name: listaunidad listaunidad_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.listaunidad
    ADD CONSTRAINT listaunidad_pkey PRIMARY KEY (id);


--
-- TOC entry 5358 (class 2606 OID 159904)
-- Name: marca marca_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.marca
    ADD CONSTRAINT marca_pkey PRIMARY KEY (id);


--
-- TOC entry 5360 (class 2606 OID 159906)
-- Name: mediopago mediopago_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mediopago
    ADD CONSTRAINT mediopago_pkey PRIMARY KEY (id);


--
-- TOC entry 5362 (class 2606 OID 159908)
-- Name: menuopciones menuopciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.menuopciones
    ADD CONSTRAINT menuopciones_pkey PRIMARY KEY (id);


--
-- TOC entry 5364 (class 2606 OID 159910)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 5366 (class 2606 OID 159912)
-- Name: modulos modulos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.modulos
    ADD CONSTRAINT modulos_pkey PRIMARY KEY (id);


--
-- TOC entry 5368 (class 2606 OID 159914)
-- Name: motivomovimiento motivomovimiento_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.motivomovimiento
    ADD CONSTRAINT motivomovimiento_pkey PRIMARY KEY (id);


--
-- TOC entry 5370 (class 2606 OID 159916)
-- Name: movalmacen movalmacen_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movalmacen
    ADD CONSTRAINT movalmacen_pkey PRIMARY KEY (id);


--
-- TOC entry 5372 (class 2606 OID 159918)
-- Name: movcaja movcaja_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movcaja
    ADD CONSTRAINT movcaja_pkey PRIMARY KEY (id);


--
-- TOC entry 5374 (class 2606 OID 159920)
-- Name: movhabitacion movhabitacion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movhabitacion
    ADD CONSTRAINT movhabitacion_pkey PRIMARY KEY (id);


--
-- TOC entry 5376 (class 2606 OID 159922)
-- Name: movimiento movimiento_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimiento
    ADD CONSTRAINT movimiento_pkey PRIMARY KEY (id);


--
-- TOC entry 5378 (class 2606 OID 159924)
-- Name: movventa movventa_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movventa
    ADD CONSTRAINT movventa_pkey PRIMARY KEY (id);


--
-- TOC entry 5380 (class 2606 OID 159926)
-- Name: operaciones operaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operaciones
    ADD CONSTRAINT operaciones_pkey PRIMARY KEY (id);


--
-- TOC entry 5382 (class 2606 OID 159928)
-- Name: parametros parametros_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parametros
    ADD CONSTRAINT parametros_pkey PRIMARY KEY (id);


--
-- TOC entry 5384 (class 2606 OID 159930)
-- Name: pasarelapago pasarelapago_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pasarelapago
    ADD CONSTRAINT pasarelapago_pkey PRIMARY KEY (id);


--
-- TOC entry 5386 (class 2606 OID 159932)
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- TOC entry 5388 (class 2606 OID 159934)
-- Name: perfiles perfiles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.perfiles
    ADD CONSTRAINT perfiles_pkey PRIMARY KEY (id);


--
-- TOC entry 5390 (class 2606 OID 159936)
-- Name: perfilsucursal perfilsucursal_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.perfilsucursal
    ADD CONSTRAINT perfilsucursal_pkey PRIMARY KEY (id);


--
-- TOC entry 5392 (class 2606 OID 159938)
-- Name: permisousuario permisousuario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permisousuario
    ADD CONSTRAINT permisousuario_pkey PRIMARY KEY (id);


--
-- TOC entry 5394 (class 2606 OID 159940)
-- Name: persona persona_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.persona
    ADD CONSTRAINT persona_pkey PRIMARY KEY (id);


--
-- TOC entry 5396 (class 2606 OID 159942)
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- TOC entry 5398 (class 2606 OID 159944)
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- TOC entry 5401 (class 2606 OID 159946)
-- Name: personamaestro personamaestro_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personamaestro
    ADD CONSTRAINT personamaestro_pkey PRIMARY KEY (id);


--
-- TOC entry 5403 (class 2606 OID 159948)
-- Name: piso piso_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.piso
    ADD CONSTRAINT piso_pkey PRIMARY KEY (id);


--
-- TOC entry 5405 (class 2606 OID 159950)
-- Name: producto producto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_pkey PRIMARY KEY (id);


--
-- TOC entry 5407 (class 2606 OID 159952)
-- Name: rel_camposucursal rel_camposucursal_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_camposucursal
    ADD CONSTRAINT rel_camposucursal_pkey PRIMARY KEY (id);


--
-- TOC entry 5409 (class 2606 OID 159954)
-- Name: rel_categoriasucursal rel_categoriasucursal_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_categoriasucursal
    ADD CONSTRAINT rel_categoriasucursal_pkey PRIMARY KEY (id);


--
-- TOC entry 5411 (class 2606 OID 159956)
-- Name: rel_listaunidadsucursal rel_listaunidadsucursal_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_listaunidadsucursal
    ADD CONSTRAINT rel_listaunidadsucursal_pkey PRIMARY KEY (id);


--
-- TOC entry 5413 (class 2606 OID 159958)
-- Name: rel_mediopagopasarela rel_mediopagopasarela_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_mediopagopasarela
    ADD CONSTRAINT rel_mediopagopasarela_pkey PRIMARY KEY (id);


--
-- TOC entry 5415 (class 2606 OID 159960)
-- Name: rel_operacionsucursal rel_operacionsucursal_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_operacionsucursal
    ADD CONSTRAINT rel_operacionsucursal_pkey PRIMARY KEY (id);


--
-- TOC entry 5417 (class 2606 OID 159962)
-- Name: rel_operacionsucursalperfil rel_operacionsucursalperfil_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_operacionsucursalperfil
    ADD CONSTRAINT rel_operacionsucursalperfil_pkey PRIMARY KEY (id);


--
-- TOC entry 5419 (class 2606 OID 159964)
-- Name: rel_parametroempresa rel_parametroempresa_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_parametroempresa
    ADD CONSTRAINT rel_parametroempresa_pkey PRIMARY KEY (id);


--
-- TOC entry 5421 (class 2606 OID 159966)
-- Name: rel_parametrosucursal rel_parametrosucursal_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_parametrosucursal
    ADD CONSTRAINT rel_parametrosucursal_pkey PRIMARY KEY (id);


--
-- TOC entry 5423 (class 2606 OID 159968)
-- Name: rel_productosucursal rel_productosucursal_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_productosucursal
    ADD CONSTRAINT rel_productosucursal_pkey PRIMARY KEY (id);


--
-- TOC entry 5489 (class 2606 OID 200296)
-- Name: rel_seccionopcionmenu rel_seccionopcionmenu_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionopcionmenu
    ADD CONSTRAINT rel_seccionopcionmenu_pkey PRIMARY KEY (id);


--
-- TOC entry 5425 (class 2606 OID 159970)
-- Name: rel_seccionsucursal rel_seccionsucursal_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionsucursal
    ADD CONSTRAINT rel_seccionsucursal_pkey PRIMARY KEY (id);


--
-- TOC entry 5427 (class 2606 OID 159972)
-- Name: rel_seccionsucursalperfil rel_seccionsucursalperfil_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionsucursalperfil
    ADD CONSTRAINT rel_seccionsucursalperfil_pkey PRIMARY KEY (id);


--
-- TOC entry 5429 (class 2606 OID 159974)
-- Name: rel_tablasucursal rel_tablasucursal_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_tablasucursal
    ADD CONSTRAINT rel_tablasucursal_pkey PRIMARY KEY (id);


--
-- TOC entry 5431 (class 2606 OID 159976)
-- Name: rel_turnocaja rel_turnocaja_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_turnocaja
    ADD CONSTRAINT rel_turnocaja_pkey PRIMARY KEY (id);


--
-- TOC entry 5433 (class 2606 OID 159978)
-- Name: reserva reserva_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_pkey PRIMARY KEY (id);


--
-- TOC entry 5483 (class 2606 OID 192171)
-- Name: respuesta respuesta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.respuesta
    ADD CONSTRAINT respuesta_pkey PRIMARY KEY (id);


--
-- TOC entry 5435 (class 2606 OID 159980)
-- Name: rol rol_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rol
    ADD CONSTRAINT rol_pkey PRIMARY KEY (id);


--
-- TOC entry 5437 (class 2606 OID 159982)
-- Name: secciones secciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secciones
    ADD CONSTRAINT secciones_pkey PRIMARY KEY (id);


--
-- TOC entry 5440 (class 2606 OID 159984)
-- Name: solicitud solicitud_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitud
    ADD CONSTRAINT solicitud_pkey PRIMARY KEY (id);


--
-- TOC entry 5443 (class 2606 OID 159986)
-- Name: stockproducto stockproducto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stockproducto
    ADD CONSTRAINT stockproducto_pkey PRIMARY KEY (id);


--
-- TOC entry 5445 (class 2606 OID 159988)
-- Name: subalmacen subalmacen_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subalmacen
    ADD CONSTRAINT subalmacen_pkey PRIMARY KEY (id);


--
-- TOC entry 5447 (class 2606 OID 159990)
-- Name: sucursal sucursal_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sucursal
    ADD CONSTRAINT sucursal_pkey PRIMARY KEY (id);


--
-- TOC entry 5449 (class 2606 OID 159992)
-- Name: tablas tablas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tablas
    ADD CONSTRAINT tablas_pkey PRIMARY KEY (id);


--
-- TOC entry 5451 (class 2606 OID 159994)
-- Name: tarjeta tarjeta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tarjeta
    ADD CONSTRAINT tarjeta_pkey PRIMARY KEY (id);


--
-- TOC entry 5453 (class 2606 OID 159996)
-- Name: tipodocumento tipodocumento_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipodocumento
    ADD CONSTRAINT tipodocumento_pkey PRIMARY KEY (id);


--
-- TOC entry 5455 (class 2606 OID 159998)
-- Name: tipohabitacion tipohabitacion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipohabitacion
    ADD CONSTRAINT tipohabitacion_pkey PRIMARY KEY (id);


--
-- TOC entry 5457 (class 2606 OID 160000)
-- Name: tipomovimiento tipomovimiento_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipomovimiento
    ADD CONSTRAINT tipomovimiento_pkey PRIMARY KEY (id);


--
-- TOC entry 5459 (class 2606 OID 160002)
-- Name: tipoopciones tipoopciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipoopciones
    ADD CONSTRAINT tipoopciones_pkey PRIMARY KEY (id);


--
-- TOC entry 5479 (class 2606 OID 192130)
-- Name: tipopregunta tipopregunta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipopregunta
    ADD CONSTRAINT tipopregunta_pkey PRIMARY KEY (id);


--
-- TOC entry 5461 (class 2606 OID 160004)
-- Name: turno turno_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.turno
    ADD CONSTRAINT turno_pkey PRIMARY KEY (id);


--
-- TOC entry 5463 (class 2606 OID 160006)
-- Name: ubicacion ubicacion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubicacion
    ADD CONSTRAINT ubicacion_pkey PRIMARY KEY (id);


--
-- TOC entry 5465 (class 2606 OID 160008)
-- Name: ubigeo ubigeos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubigeo
    ADD CONSTRAINT ubigeos_pkey PRIMARY KEY (id);


--
-- TOC entry 5467 (class 2606 OID 160010)
-- Name: unidad unidad_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.unidad
    ADD CONSTRAINT unidad_pkey PRIMARY KEY (id);


--
-- TOC entry 5469 (class 2606 OID 160012)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 5471 (class 2606 OID 160014)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 5487 (class 2606 OID 192217)
-- Name: valordet_respuesta valordet_respuesta_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.valordet_respuesta
    ADD CONSTRAINT valordet_respuesta_pkey PRIMARY KEY (id);


--
-- TOC entry 5399 (class 1259 OID 160015)
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- TOC entry 5438 (class 1259 OID 160016)
-- Name: solicitud_estado_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX solicitud_estado_index ON public.solicitud USING btree (estado);


--
-- TOC entry 5441 (class 1259 OID 160017)
-- Name: solicitud_tipo_documento_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX solicitud_tipo_documento_index ON public.solicitud USING btree (tipo_documento);


--
-- TOC entry 5647 (class 2620 OID 160018)
-- Name: det_movventa after_insert_det_movventa; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER after_insert_det_movventa AFTER INSERT ON public.det_movventa FOR EACH ROW EXECUTE FUNCTION public.trigger_insert_kardex();


--
-- TOC entry 5490 (class 2606 OID 160019)
-- Name: caja caja_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.caja
    ADD CONSTRAINT caja_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5491 (class 2606 OID 160024)
-- Name: campos campos_tabla_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.campos
    ADD CONSTRAINT campos_tabla_id_foreign FOREIGN KEY (tabla_id) REFERENCES public.tablas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5492 (class 2606 OID 160029)
-- Name: categoria categoria_categoria_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoria
    ADD CONSTRAINT categoria_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES public.categoria(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 5493 (class 2606 OID 160034)
-- Name: categoria categoria_empresa_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoria
    ADD CONSTRAINT categoria_empresa_id_foreign FOREIGN KEY (empresa_id) REFERENCES public.empresas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5631 (class 2606 OID 192082)
-- Name: categoriapregunta categoriapregunta_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categoriapregunta
    ADD CONSTRAINT categoriapregunta_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5633 (class 2606 OID 192143)
-- Name: det_encuesta det_encuesta_categoriapregunta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_encuesta
    ADD CONSTRAINT det_encuesta_categoriapregunta_id_foreign FOREIGN KEY (categoriapregunta_id) REFERENCES public.categoriapregunta(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5634 (class 2606 OID 192153)
-- Name: det_encuesta det_encuesta_encuesta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_encuesta
    ADD CONSTRAINT det_encuesta_encuesta_id_foreign FOREIGN KEY (encuesta_id) REFERENCES public.encuesta(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5635 (class 2606 OID 192158)
-- Name: det_encuesta det_encuesta_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_encuesta
    ADD CONSTRAINT det_encuesta_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5636 (class 2606 OID 192148)
-- Name: det_encuesta det_encuesta_tipopregunta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_encuesta
    ADD CONSTRAINT det_encuesta_tipopregunta_id_foreign FOREIGN KEY (tipopregunta_id) REFERENCES public.tipopregunta(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5494 (class 2606 OID 160039)
-- Name: det_movalmacen det_movalmacen_movalmacen_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movalmacen
    ADD CONSTRAINT det_movalmacen_movalmacen_id_foreign FOREIGN KEY (movalmacen_id) REFERENCES public.movalmacen(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5495 (class 2606 OID 160044)
-- Name: det_movalmacen det_movalmacen_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movalmacen
    ADD CONSTRAINT det_movalmacen_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5496 (class 2606 OID 160049)
-- Name: det_movcaja det_movcaja_banco_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movcaja
    ADD CONSTRAINT det_movcaja_banco_id_foreign FOREIGN KEY (banco_id) REFERENCES public.banco(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5497 (class 2606 OID 160054)
-- Name: det_movcaja det_movcaja_billeteradigital_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movcaja
    ADD CONSTRAINT det_movcaja_billeteradigital_id_foreign FOREIGN KEY (billeteradigital_id) REFERENCES public.billeteradigital(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5498 (class 2606 OID 160059)
-- Name: det_movcaja det_movcaja_mediopago_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movcaja
    ADD CONSTRAINT det_movcaja_mediopago_id_foreign FOREIGN KEY (mediopago_id) REFERENCES public.mediopago(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5499 (class 2606 OID 160064)
-- Name: det_movcaja det_movcaja_movcaja_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movcaja
    ADD CONSTRAINT det_movcaja_movcaja_id_foreign FOREIGN KEY (movcaja_id) REFERENCES public.movcaja(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5500 (class 2606 OID 160069)
-- Name: det_movcaja det_movcaja_pos_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movcaja
    ADD CONSTRAINT det_movcaja_pos_id_foreign FOREIGN KEY (pos_id) REFERENCES public.pasarelapago(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5501 (class 2606 OID 160074)
-- Name: det_movcaja det_movcaja_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movcaja
    ADD CONSTRAINT det_movcaja_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5502 (class 2606 OID 160079)
-- Name: det_movcaja det_movcaja_tarjeta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movcaja
    ADD CONSTRAINT det_movcaja_tarjeta_id_foreign FOREIGN KEY (tarjeta_id) REFERENCES public.tarjeta(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5503 (class 2606 OID 160084)
-- Name: det_movhabitacion det_movhabitacion_det_movventa_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movhabitacion
    ADD CONSTRAINT det_movhabitacion_det_movventa_id_foreign FOREIGN KEY (det_movventa_id) REFERENCES public.det_movventa(id);


--
-- TOC entry 5504 (class 2606 OID 160089)
-- Name: det_movhabitacion det_movhabitacion_igv_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movhabitacion
    ADD CONSTRAINT det_movhabitacion_igv_id_foreign FOREIGN KEY (igv_id) REFERENCES public.igv(id);


--
-- TOC entry 5505 (class 2606 OID 160094)
-- Name: det_movhabitacion det_movhabitacion_movhabitacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movhabitacion
    ADD CONSTRAINT det_movhabitacion_movhabitacion_id_foreign FOREIGN KEY (movhabitacion_id) REFERENCES public.movhabitacion(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5506 (class 2606 OID 160099)
-- Name: det_movhabitacion det_movhabitacion_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movhabitacion
    ADD CONSTRAINT det_movhabitacion_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.producto(id);


--
-- TOC entry 5507 (class 2606 OID 160104)
-- Name: det_movhabitacion det_movhabitacion_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movhabitacion
    ADD CONSTRAINT det_movhabitacion_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5508 (class 2606 OID 160109)
-- Name: det_movhabitacion det_movhabitacion_unidad_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movhabitacion
    ADD CONSTRAINT det_movhabitacion_unidad_id_foreign FOREIGN KEY (unidad_id) REFERENCES public.unidad(id);


--
-- TOC entry 5509 (class 2606 OID 160114)
-- Name: det_movventa det_movventa_det_movventa_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movventa
    ADD CONSTRAINT det_movventa_det_movventa_id_foreign FOREIGN KEY (det_movventa_id) REFERENCES public.det_movventa(id);


--
-- TOC entry 5510 (class 2606 OID 160119)
-- Name: det_movventa det_movventa_igv_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movventa
    ADD CONSTRAINT det_movventa_igv_id_foreign FOREIGN KEY (igv_id) REFERENCES public.igv(id);


--
-- TOC entry 5511 (class 2606 OID 160124)
-- Name: det_movventa det_movventa_movventa_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movventa
    ADD CONSTRAINT det_movventa_movventa_id_foreign FOREIGN KEY (movventa_id) REFERENCES public.movventa(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5512 (class 2606 OID 160129)
-- Name: det_movventa det_movventa_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movventa
    ADD CONSTRAINT det_movventa_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.producto(id);


--
-- TOC entry 5513 (class 2606 OID 160134)
-- Name: det_movventa det_movventa_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movventa
    ADD CONSTRAINT det_movventa_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5514 (class 2606 OID 160139)
-- Name: det_movventa det_movventa_unidad_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_movventa
    ADD CONSTRAINT det_movventa_unidad_id_foreign FOREIGN KEY (unidad_id) REFERENCES public.unidad(id);


--
-- TOC entry 5640 (class 2606 OID 192194)
-- Name: det_respuesta det_respuesta_det_encuesta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_respuesta
    ADD CONSTRAINT det_respuesta_det_encuesta_id_foreign FOREIGN KEY (det_encuesta_id) REFERENCES public.det_encuesta(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5641 (class 2606 OID 192199)
-- Name: det_respuesta det_respuesta_respuesta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_respuesta
    ADD CONSTRAINT det_respuesta_respuesta_id_foreign FOREIGN KEY (respuesta_id) REFERENCES public.respuesta(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5642 (class 2606 OID 192204)
-- Name: det_respuesta det_respuesta_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.det_respuesta
    ADD CONSTRAINT det_respuesta_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5632 (class 2606 OID 192116)
-- Name: encuesta encuesta_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.encuesta
    ADD CONSTRAINT encuesta_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5515 (class 2606 OID 160144)
-- Name: facturador facturador_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.facturador
    ADD CONSTRAINT facturador_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON DELETE CASCADE;


--
-- TOC entry 5516 (class 2606 OID 160149)
-- Name: habitacion fk_habitacion_piso; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.habitacion
    ADD CONSTRAINT fk_habitacion_piso FOREIGN KEY (piso_id) REFERENCES public.piso(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5517 (class 2606 OID 160154)
-- Name: habitacion fk_habitacion_tipohabitacion; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.habitacion
    ADD CONSTRAINT fk_habitacion_tipohabitacion FOREIGN KEY (tipohabitacion_id) REFERENCES public.tipohabitacion(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5518 (class 2606 OID 160159)
-- Name: habitacion habitacion_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.habitacion
    ADD CONSTRAINT habitacion_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5628 (class 2606 OID 183892)
-- Name: historial_habitacion historial_habitacion_movhabitacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historial_habitacion
    ADD CONSTRAINT historial_habitacion_movhabitacion_id_foreign FOREIGN KEY (movhabitacion_id) REFERENCES public.movhabitacion(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5629 (class 2606 OID 183897)
-- Name: historial_habitacion historial_habitacion_relturnocaja_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historial_habitacion
    ADD CONSTRAINT historial_habitacion_relturnocaja_id_foreign FOREIGN KEY (relturnocaja_id) REFERENCES public.rel_turnocaja(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5630 (class 2606 OID 183902)
-- Name: historial_habitacion historial_habitacion_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historial_habitacion
    ADD CONSTRAINT historial_habitacion_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5519 (class 2606 OID 160164)
-- Name: huesped huesped_movhabitacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.huesped
    ADD CONSTRAINT huesped_movhabitacion_id_foreign FOREIGN KEY (movhabitacion_id) REFERENCES public.movhabitacion(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5520 (class 2606 OID 160169)
-- Name: huesped huesped_persona_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.huesped
    ADD CONSTRAINT huesped_persona_id_foreign FOREIGN KEY (persona_id) REFERENCES public.persona(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5521 (class 2606 OID 160174)
-- Name: kardex kardex_detalle_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kardex
    ADD CONSTRAINT kardex_detalle_id_foreign FOREIGN KEY (detalle_id) REFERENCES public.det_movventa(id);


--
-- TOC entry 5522 (class 2606 OID 160179)
-- Name: kardex kardex_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kardex
    ADD CONSTRAINT kardex_movimiento_id_foreign FOREIGN KEY (movimiento_id) REFERENCES public.movimiento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5523 (class 2606 OID 160184)
-- Name: kardex kardex_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kardex
    ADD CONSTRAINT kardex_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.producto(id);


--
-- TOC entry 5524 (class 2606 OID 160189)
-- Name: kardex kardex_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kardex
    ADD CONSTRAINT kardex_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5525 (class 2606 OID 160194)
-- Name: kardex kardex_tipodocumento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kardex
    ADD CONSTRAINT kardex_tipodocumento_id_foreign FOREIGN KEY (tipodocumento_id) REFERENCES public.tipodocumento(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5526 (class 2606 OID 160199)
-- Name: kardex kardex_tipomovimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kardex
    ADD CONSTRAINT kardex_tipomovimiento_id_foreign FOREIGN KEY (tipomovimiento_id) REFERENCES public.tipomovimiento(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5527 (class 2606 OID 160204)
-- Name: kardex kardex_unidad_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kardex
    ADD CONSTRAINT kardex_unidad_id_foreign FOREIGN KEY (unidad_id) REFERENCES public.unidad(id);


--
-- TOC entry 5528 (class 2606 OID 160209)
-- Name: kardex kardex_unidadbase_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kardex
    ADD CONSTRAINT kardex_unidadbase_id_foreign FOREIGN KEY (unidadbase_id) REFERENCES public.unidad(id);


--
-- TOC entry 5529 (class 2606 OID 160214)
-- Name: kardex kardex_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kardex
    ADD CONSTRAINT kardex_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.users(id);


--
-- TOC entry 5530 (class 2606 OID 160219)
-- Name: listaunidad listaunidad_empresa_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.listaunidad
    ADD CONSTRAINT listaunidad_empresa_id_foreign FOREIGN KEY (empresa_id) REFERENCES public.empresas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5531 (class 2606 OID 160224)
-- Name: listaunidad listaunidad_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.listaunidad
    ADD CONSTRAINT listaunidad_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.producto(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5532 (class 2606 OID 160229)
-- Name: listaunidad listaunidad_unidad_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.listaunidad
    ADD CONSTRAINT listaunidad_unidad_id_foreign FOREIGN KEY (unidad_id) REFERENCES public.unidad(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5533 (class 2606 OID 160234)
-- Name: listaunidad listaunidad_unidadbase_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.listaunidad
    ADD CONSTRAINT listaunidad_unidadbase_id_foreign FOREIGN KEY (unidadbase_id) REFERENCES public.unidad(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5534 (class 2606 OID 160239)
-- Name: menuopciones menuopciones_modulo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.menuopciones
    ADD CONSTRAINT menuopciones_modulo_id_foreign FOREIGN KEY (modulo_id) REFERENCES public.modulos(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5535 (class 2606 OID 160244)
-- Name: menuopciones menuopciones_tabla_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.menuopciones
    ADD CONSTRAINT menuopciones_tabla_id_foreign FOREIGN KEY (tabla_id) REFERENCES public.tablas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5536 (class 2606 OID 160249)
-- Name: menuopciones menuopciones_tipoopcion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.menuopciones
    ADD CONSTRAINT menuopciones_tipoopcion_id_foreign FOREIGN KEY (tipoopcion_id) REFERENCES public.tipoopciones(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5537 (class 2606 OID 160254)
-- Name: motivomovimiento motivomovimiento_tipomovimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.motivomovimiento
    ADD CONSTRAINT motivomovimiento_tipomovimiento_id_foreign FOREIGN KEY (tipomovimiento_id) REFERENCES public.tipomovimiento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5538 (class 2606 OID 160259)
-- Name: movalmacen movalmacen_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movalmacen
    ADD CONSTRAINT movalmacen_movimiento_id_foreign FOREIGN KEY (movimiento_id) REFERENCES public.movimiento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5539 (class 2606 OID 160264)
-- Name: movalmacen movalmacen_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movalmacen
    ADD CONSTRAINT movalmacen_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5540 (class 2606 OID 160269)
-- Name: movcaja movcaja_caja_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movcaja
    ADD CONSTRAINT movcaja_caja_id_foreign FOREIGN KEY (caja_id) REFERENCES public.caja(id);


--
-- TOC entry 5541 (class 2606 OID 160274)
-- Name: movcaja movcaja_conceptopago_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movcaja
    ADD CONSTRAINT movcaja_conceptopago_id_foreign FOREIGN KEY (conceptopago_id) REFERENCES public.conceptopago(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5542 (class 2606 OID 160279)
-- Name: movcaja movcaja_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movcaja
    ADD CONSTRAINT movcaja_movimiento_id_foreign FOREIGN KEY (movimiento_id) REFERENCES public.movimiento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5543 (class 2606 OID 160284)
-- Name: movcaja movcaja_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movcaja
    ADD CONSTRAINT movcaja_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5544 (class 2606 OID 160289)
-- Name: movhabitacion movhabitacion_reserva_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movhabitacion
    ADD CONSTRAINT movhabitacion_reserva_id_foreign FOREIGN KEY (reserva_id) REFERENCES public.reserva(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5545 (class 2606 OID 160294)
-- Name: movhabitacion movhabitacion_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movhabitacion
    ADD CONSTRAINT movhabitacion_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5546 (class 2606 OID 160299)
-- Name: movimiento movimiento_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimiento
    ADD CONSTRAINT movimiento_movimiento_id_foreign FOREIGN KEY (movimiento_id) REFERENCES public.movimiento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5547 (class 2606 OID 160304)
-- Name: movimiento movimiento_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimiento
    ADD CONSTRAINT movimiento_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5548 (class 2606 OID 160309)
-- Name: movimiento movimiento_tipodocumento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimiento
    ADD CONSTRAINT movimiento_tipodocumento_id_foreign FOREIGN KEY (tipodocumento_id) REFERENCES public.tipodocumento(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5549 (class 2606 OID 160314)
-- Name: movimiento movimiento_tipomovimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimiento
    ADD CONSTRAINT movimiento_tipomovimiento_id_foreign FOREIGN KEY (tipomovimiento_id) REFERENCES public.tipomovimiento(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5550 (class 2606 OID 160319)
-- Name: movventa movventa_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movventa
    ADD CONSTRAINT movventa_movimiento_id_foreign FOREIGN KEY (movimiento_id) REFERENCES public.movimiento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5551 (class 2606 OID 160324)
-- Name: movventa movventa_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movventa
    ADD CONSTRAINT movventa_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5552 (class 2606 OID 160329)
-- Name: operaciones operaciones_tabla_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operaciones
    ADD CONSTRAINT operaciones_tabla_id_foreign FOREIGN KEY (tabla_id) REFERENCES public.tablas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5553 (class 2606 OID 160334)
-- Name: parametros parametros_categoriaparametro_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parametros
    ADD CONSTRAINT parametros_categoriaparametro_id_foreign FOREIGN KEY (categoriaparametro_id) REFERENCES public.categoriaparametros(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5554 (class 2606 OID 160339)
-- Name: parametros parametros_tabla_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parametros
    ADD CONSTRAINT parametros_tabla_id_foreign FOREIGN KEY (tabla_id) REFERENCES public.tablas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5555 (class 2606 OID 160344)
-- Name: perfilsucursal perfilsucursal_perfil_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.perfilsucursal
    ADD CONSTRAINT perfilsucursal_perfil_id_foreign FOREIGN KEY (perfil_id) REFERENCES public.perfiles(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5556 (class 2606 OID 160349)
-- Name: perfilsucursal perfilsucursal_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.perfilsucursal
    ADD CONSTRAINT perfilsucursal_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5557 (class 2606 OID 160354)
-- Name: permisousuario permisousuario_menuopcion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permisousuario
    ADD CONSTRAINT permisousuario_menuopcion_id_foreign FOREIGN KEY (menuopcion_id) REFERENCES public.menuopciones(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5558 (class 2606 OID 160359)
-- Name: permisousuario permisousuario_perfil_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permisousuario
    ADD CONSTRAINT permisousuario_perfil_id_foreign FOREIGN KEY (perfil_id) REFERENCES public.perfiles(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5559 (class 2606 OID 160364)
-- Name: permisousuario permisousuario_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permisousuario
    ADD CONSTRAINT permisousuario_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5560 (class 2606 OID 160369)
-- Name: persona persona_distrito_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.persona
    ADD CONSTRAINT persona_distrito_id_foreign FOREIGN KEY (distrito_id) REFERENCES public.ubigeo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5561 (class 2606 OID 160374)
-- Name: persona persona_personamaestro_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.persona
    ADD CONSTRAINT persona_personamaestro_id_foreign FOREIGN KEY (personamaestro_id) REFERENCES public.personamaestro(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5562 (class 2606 OID 160379)
-- Name: persona persona_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.persona
    ADD CONSTRAINT persona_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5563 (class 2606 OID 160384)
-- Name: piso piso_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.piso
    ADD CONSTRAINT piso_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5564 (class 2606 OID 160389)
-- Name: producto producto_categoria_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES public.categoria(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5565 (class 2606 OID 160394)
-- Name: producto producto_distribuidor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_distribuidor_id_foreign FOREIGN KEY (distribuidor_id) REFERENCES public.distribuidor(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 5566 (class 2606 OID 160399)
-- Name: producto producto_empresa_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_empresa_id_foreign FOREIGN KEY (empresa_id) REFERENCES public.empresas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5567 (class 2606 OID 160404)
-- Name: producto producto_marca_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_marca_id_foreign FOREIGN KEY (marca_id) REFERENCES public.marca(id) ON UPDATE RESTRICT ON DELETE SET NULL;


--
-- TOC entry 5568 (class 2606 OID 160409)
-- Name: producto producto_medidapeso_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_medidapeso_id_foreign FOREIGN KEY (medidapeso_id) REFERENCES public.unidad(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5569 (class 2606 OID 160414)
-- Name: producto producto_unidadbase_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.producto
    ADD CONSTRAINT producto_unidadbase_id_foreign FOREIGN KEY (unidadbase_id) REFERENCES public.unidad(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5570 (class 2606 OID 160419)
-- Name: rel_camposucursal rel_camposucursal_campo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_camposucursal
    ADD CONSTRAINT rel_camposucursal_campo_id_foreign FOREIGN KEY (campo_id) REFERENCES public.campos(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5571 (class 2606 OID 160424)
-- Name: rel_camposucursal rel_camposucursal_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_camposucursal
    ADD CONSTRAINT rel_camposucursal_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5572 (class 2606 OID 160429)
-- Name: rel_camposucursal rel_camposucursal_tabla_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_camposucursal
    ADD CONSTRAINT rel_camposucursal_tabla_id_foreign FOREIGN KEY (tabla_id) REFERENCES public.tablas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5573 (class 2606 OID 160434)
-- Name: rel_categoriasucursal rel_categoriasucursal_categoria_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_categoriasucursal
    ADD CONSTRAINT rel_categoriasucursal_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES public.categoria(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5574 (class 2606 OID 160439)
-- Name: rel_categoriasucursal rel_categoriasucursal_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_categoriasucursal
    ADD CONSTRAINT rel_categoriasucursal_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5575 (class 2606 OID 160444)
-- Name: rel_listaunidadsucursal rel_listaunidadsucursal_listaunidad_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_listaunidadsucursal
    ADD CONSTRAINT rel_listaunidadsucursal_listaunidad_id_foreign FOREIGN KEY (listaunidad_id) REFERENCES public.listaunidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5576 (class 2606 OID 160449)
-- Name: rel_listaunidadsucursal rel_listaunidadsucursal_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_listaunidadsucursal
    ADD CONSTRAINT rel_listaunidadsucursal_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5577 (class 2606 OID 160454)
-- Name: rel_mediopagopasarela rel_mediopagopasarela_medio_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_mediopagopasarela
    ADD CONSTRAINT rel_mediopagopasarela_medio_id_foreign FOREIGN KEY (medio_id) REFERENCES public.mediopago(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5578 (class 2606 OID 160459)
-- Name: rel_mediopagopasarela rel_mediopagopasarela_pasarela_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_mediopagopasarela
    ADD CONSTRAINT rel_mediopagopasarela_pasarela_id_foreign FOREIGN KEY (pasarela_id) REFERENCES public.pasarelapago(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5579 (class 2606 OID 160464)
-- Name: rel_operacionsucursal rel_operacionsucursal_operacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_operacionsucursal
    ADD CONSTRAINT rel_operacionsucursal_operacion_id_foreign FOREIGN KEY (operacion_id) REFERENCES public.operaciones(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5580 (class 2606 OID 160469)
-- Name: rel_operacionsucursal rel_operacionsucursal_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_operacionsucursal
    ADD CONSTRAINT rel_operacionsucursal_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5581 (class 2606 OID 160474)
-- Name: rel_operacionsucursal rel_operacionsucursal_tabla_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_operacionsucursal
    ADD CONSTRAINT rel_operacionsucursal_tabla_id_foreign FOREIGN KEY (tabla_id) REFERENCES public.tablas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5582 (class 2606 OID 160479)
-- Name: rel_operacionsucursalperfil rel_operacionsucursalperfil_operacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_operacionsucursalperfil
    ADD CONSTRAINT rel_operacionsucursalperfil_operacion_id_foreign FOREIGN KEY (operacion_id) REFERENCES public.operaciones(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5583 (class 2606 OID 160484)
-- Name: rel_operacionsucursalperfil rel_operacionsucursalperfil_perfil_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_operacionsucursalperfil
    ADD CONSTRAINT rel_operacionsucursalperfil_perfil_id_foreign FOREIGN KEY (perfil_id) REFERENCES public.perfiles(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5584 (class 2606 OID 160489)
-- Name: rel_operacionsucursalperfil rel_operacionsucursalperfil_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_operacionsucursalperfil
    ADD CONSTRAINT rel_operacionsucursalperfil_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5585 (class 2606 OID 160494)
-- Name: rel_operacionsucursalperfil rel_operacionsucursalperfil_tabla_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_operacionsucursalperfil
    ADD CONSTRAINT rel_operacionsucursalperfil_tabla_id_foreign FOREIGN KEY (tabla_id) REFERENCES public.tablas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5586 (class 2606 OID 160499)
-- Name: rel_parametroempresa rel_parametroempresa_empresa_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_parametroempresa
    ADD CONSTRAINT rel_parametroempresa_empresa_id_foreign FOREIGN KEY (empresa_id) REFERENCES public.empresas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5587 (class 2606 OID 160504)
-- Name: rel_parametroempresa rel_parametroempresa_parametro_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_parametroempresa
    ADD CONSTRAINT rel_parametroempresa_parametro_id_foreign FOREIGN KEY (parametro_id) REFERENCES public.parametros(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5588 (class 2606 OID 160509)
-- Name: rel_parametrosucursal rel_parametrosucursal_parametro_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_parametrosucursal
    ADD CONSTRAINT rel_parametrosucursal_parametro_id_foreign FOREIGN KEY (parametro_id) REFERENCES public.parametros(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5589 (class 2606 OID 160514)
-- Name: rel_parametrosucursal rel_parametrosucursal_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_parametrosucursal
    ADD CONSTRAINT rel_parametrosucursal_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5590 (class 2606 OID 160519)
-- Name: rel_productosucursal rel_productosucursal_igv_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_productosucursal
    ADD CONSTRAINT rel_productosucursal_igv_id_foreign FOREIGN KEY (igv_id) REFERENCES public.igv(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 5591 (class 2606 OID 160524)
-- Name: rel_productosucursal rel_productosucursal_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_productosucursal
    ADD CONSTRAINT rel_productosucursal_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.producto(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5592 (class 2606 OID 160529)
-- Name: rel_productosucursal rel_productosucursal_proveedor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_productosucursal
    ADD CONSTRAINT rel_productosucursal_proveedor_id_foreign FOREIGN KEY (proveedor_id) REFERENCES public.persona(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 5593 (class 2606 OID 160534)
-- Name: rel_productosucursal rel_productosucursal_subalmacen_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_productosucursal
    ADD CONSTRAINT rel_productosucursal_subalmacen_id_foreign FOREIGN KEY (subalmacen_id) REFERENCES public.subalmacen(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 5594 (class 2606 OID 160539)
-- Name: rel_productosucursal rel_productosucursal_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_productosucursal
    ADD CONSTRAINT rel_productosucursal_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5595 (class 2606 OID 160544)
-- Name: rel_productosucursal rel_productosucursal_ubicacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_productosucursal
    ADD CONSTRAINT rel_productosucursal_ubicacion_id_foreign FOREIGN KEY (ubicacion_id) REFERENCES public.ubicacion(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 5645 (class 2606 OID 200302)
-- Name: rel_seccionopcionmenu rel_seccionopcionmenu_opcionmenu_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionopcionmenu
    ADD CONSTRAINT rel_seccionopcionmenu_opcionmenu_id_foreign FOREIGN KEY (opcionmenu_id) REFERENCES public.menuopciones(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5646 (class 2606 OID 200297)
-- Name: rel_seccionopcionmenu rel_seccionopcionmenu_seccion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionopcionmenu
    ADD CONSTRAINT rel_seccionopcionmenu_seccion_id_foreign FOREIGN KEY (seccion_id) REFERENCES public.secciones(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5596 (class 2606 OID 160549)
-- Name: rel_seccionsucursal rel_seccionsucursal_seccion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionsucursal
    ADD CONSTRAINT rel_seccionsucursal_seccion_id_foreign FOREIGN KEY (seccion_id) REFERENCES public.secciones(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5597 (class 2606 OID 160554)
-- Name: rel_seccionsucursal rel_seccionsucursal_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionsucursal
    ADD CONSTRAINT rel_seccionsucursal_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5598 (class 2606 OID 160559)
-- Name: rel_seccionsucursalperfil rel_seccionsucursalperfil_menuopcionesdefecto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionsucursalperfil
    ADD CONSTRAINT rel_seccionsucursalperfil_menuopcionesdefecto_id_foreign FOREIGN KEY (menuopcionesdefecto_id) REFERENCES public.menuopciones(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5599 (class 2606 OID 160564)
-- Name: rel_seccionsucursalperfil rel_seccionsucursalperfil_perfil_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionsucursalperfil
    ADD CONSTRAINT rel_seccionsucursalperfil_perfil_id_foreign FOREIGN KEY (perfil_id) REFERENCES public.perfiles(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5600 (class 2606 OID 160569)
-- Name: rel_seccionsucursalperfil rel_seccionsucursalperfil_seccion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionsucursalperfil
    ADD CONSTRAINT rel_seccionsucursalperfil_seccion_id_foreign FOREIGN KEY (seccion_id) REFERENCES public.secciones(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5601 (class 2606 OID 160574)
-- Name: rel_seccionsucursalperfil rel_seccionsucursalperfil_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_seccionsucursalperfil
    ADD CONSTRAINT rel_seccionsucursalperfil_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5602 (class 2606 OID 160579)
-- Name: rel_tablasucursal rel_tablasucursal_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_tablasucursal
    ADD CONSTRAINT rel_tablasucursal_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5603 (class 2606 OID 160584)
-- Name: rel_tablasucursal rel_tablasucursal_tabla_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_tablasucursal
    ADD CONSTRAINT rel_tablasucursal_tabla_id_foreign FOREIGN KEY (tabla_id) REFERENCES public.tablas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5604 (class 2606 OID 160589)
-- Name: rel_turnocaja rel_turnocaja_movcajafin_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_turnocaja
    ADD CONSTRAINT rel_turnocaja_movcajafin_id_foreign FOREIGN KEY (movcajafin_id) REFERENCES public.movcaja(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5605 (class 2606 OID 160594)
-- Name: rel_turnocaja rel_turnocaja_movcajainicio_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_turnocaja
    ADD CONSTRAINT rel_turnocaja_movcajainicio_id_foreign FOREIGN KEY (movcajainicio_id) REFERENCES public.movcaja(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5606 (class 2606 OID 160599)
-- Name: rel_turnocaja rel_turnocaja_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rel_turnocaja
    ADD CONSTRAINT rel_turnocaja_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5607 (class 2606 OID 160604)
-- Name: reserva reserva_habitacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_habitacion_id_foreign FOREIGN KEY (habitacion_id) REFERENCES public.habitacion(id) ON UPDATE CASCADE;


--
-- TOC entry 5608 (class 2606 OID 160609)
-- Name: reserva reserva_persona_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_persona_id_foreign FOREIGN KEY (persona_id) REFERENCES public.persona(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5609 (class 2606 OID 160614)
-- Name: reserva reserva_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reserva
    ADD CONSTRAINT reserva_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE;


--
-- TOC entry 5637 (class 2606 OID 192172)
-- Name: respuesta respuesta_encuesta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.respuesta
    ADD CONSTRAINT respuesta_encuesta_id_foreign FOREIGN KEY (encuesta_id) REFERENCES public.encuesta(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5638 (class 2606 OID 192177)
-- Name: respuesta respuesta_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.respuesta
    ADD CONSTRAINT respuesta_movimiento_id_foreign FOREIGN KEY (movimiento_id) REFERENCES public.movimiento(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 5639 (class 2606 OID 192182)
-- Name: respuesta respuesta_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.respuesta
    ADD CONSTRAINT respuesta_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5610 (class 2606 OID 160619)
-- Name: rolpersona rolpersona_persona_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rolpersona
    ADD CONSTRAINT rolpersona_persona_id_foreign FOREIGN KEY (persona_id) REFERENCES public.persona(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5611 (class 2606 OID 160624)
-- Name: rolpersona rolpersona_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rolpersona
    ADD CONSTRAINT rolpersona_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.rol(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5612 (class 2606 OID 160629)
-- Name: rolpersona rolpersona_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rolpersona
    ADD CONSTRAINT rolpersona_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5613 (class 2606 OID 160634)
-- Name: solicitud solicitud_facturador_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitud
    ADD CONSTRAINT solicitud_facturador_id_foreign FOREIGN KEY (facturador_id) REFERENCES public.facturador(id) ON DELETE CASCADE;


--
-- TOC entry 5614 (class 2606 OID 160639)
-- Name: solicitud solicitud_movimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitud
    ADD CONSTRAINT solicitud_movimiento_id_foreign FOREIGN KEY (movimiento_id) REFERENCES public.movimiento(id) ON DELETE CASCADE;


--
-- TOC entry 5615 (class 2606 OID 160644)
-- Name: solicitud solicitud_persona_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitud
    ADD CONSTRAINT solicitud_persona_id_foreign FOREIGN KEY (persona_id) REFERENCES public.persona(id) ON DELETE SET NULL;


--
-- TOC entry 5616 (class 2606 OID 160649)
-- Name: solicitud solicitud_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.solicitud
    ADD CONSTRAINT solicitud_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON DELETE CASCADE;


--
-- TOC entry 5617 (class 2606 OID 160654)
-- Name: stockproducto stockproducto_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stockproducto
    ADD CONSTRAINT stockproducto_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.producto(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5618 (class 2606 OID 160659)
-- Name: stockproducto stockproducto_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stockproducto
    ADD CONSTRAINT stockproducto_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5619 (class 2606 OID 160664)
-- Name: stockproducto stockproducto_unidadbase_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stockproducto
    ADD CONSTRAINT stockproducto_unidadbase_id_foreign FOREIGN KEY (unidadbase_id) REFERENCES public.unidad(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5620 (class 2606 OID 160669)
-- Name: sucursal sucursal_empresa_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sucursal
    ADD CONSTRAINT sucursal_empresa_id_foreign FOREIGN KEY (empresa_id) REFERENCES public.empresas(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5621 (class 2606 OID 160674)
-- Name: sucursal sucursal_ubigeo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sucursal
    ADD CONSTRAINT sucursal_ubigeo_id_foreign FOREIGN KEY (ubigeo_id) REFERENCES public.ubigeo(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 5622 (class 2606 OID 160679)
-- Name: tipodocumento tipodocumento_tipomovimiento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipodocumento
    ADD CONSTRAINT tipodocumento_tipomovimiento_id_foreign FOREIGN KEY (tipomovimiento_id) REFERENCES public.tipomovimiento(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5623 (class 2606 OID 160684)
-- Name: tipohabitacion tipohabitacion_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipohabitacion
    ADD CONSTRAINT tipohabitacion_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5624 (class 2606 OID 160689)
-- Name: turno turno_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.turno
    ADD CONSTRAINT turno_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5625 (class 2606 OID 160694)
-- Name: ubigeo ubigeos_ubigeo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ubigeo
    ADD CONSTRAINT ubigeos_ubigeo_id_foreign FOREIGN KEY (ubigeo_id) REFERENCES public.ubigeo(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5626 (class 2606 OID 160699)
-- Name: users users_perfil_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_perfil_id_foreign FOREIGN KEY (perfil_id) REFERENCES public.perfiles(id) ON DELETE SET NULL;


--
-- TOC entry 5627 (class 2606 OID 160704)
-- Name: users users_persona_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_persona_id_foreign FOREIGN KEY (persona_id) REFERENCES public.persona(id) ON DELETE SET NULL;


--
-- TOC entry 5643 (class 2606 OID 192218)
-- Name: valordet_respuesta valordet_respuesta_det_respuesta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.valordet_respuesta
    ADD CONSTRAINT valordet_respuesta_det_respuesta_id_foreign FOREIGN KEY (det_respuesta_id) REFERENCES public.det_respuesta(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5644 (class 2606 OID 192223)
-- Name: valordet_respuesta valordet_respuesta_sucursal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.valordet_respuesta
    ADD CONSTRAINT valordet_respuesta_sucursal_id_foreign FOREIGN KEY (sucursal_id) REFERENCES public.sucursal(id) ON UPDATE CASCADE ON DELETE CASCADE;


-- Completed on 2025-02-19 21:49:02

--
-- PostgreSQL database dump complete
--

