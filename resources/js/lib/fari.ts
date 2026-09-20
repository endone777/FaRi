import type { Product } from '@/types/fari';

/** Housing silhouettes used by the schematic thumbnail. */
export const SHAPES: Record<string, string> = {
    sharp: 'M96 150 L242 138 L262 166 L246 196 L104 190 Z',
    swept: 'M96 156 Q170 132 254 146 Q268 170 248 194 Q166 200 100 188 Z',
    slim: 'M98 158 L250 148 Q266 168 248 186 L102 184 Z',
};

export const SHAPE_NAMES: Record<string, string> = {
    sharp: 'Угловатая',
    swept: 'Каплевидная',
    slim: 'Узкая',
};

/** Beam tint follows the colour temperature of the lamp. */
export function beamColor(kelvin: number): string {
    if (kelvin <= 3500) {
        return '#FFC46B';
    }

    if (kelvin <= 4500) {
        return '#DDEBFF';
    }

    if (kelvin <= 5200) {
        return '#BFDBFF';
    }

    return '#9FC8FF';
}

export function money(value: number): string {
    return Number(value).toLocaleString('ru-RU') + ' ₽';
}

export function years(product: Pick<Product, 'year_from' | 'year_to'>): string {
    return `${product.year_from}–${product.year_to}`;
}

export function fitsYear(product: Product, year: number | string): boolean {
    const value = Number(year);

    return (
        Number.isFinite(value) &&
        value >= product.year_from &&
        value <= product.year_to
    );
}
