import type { Product } from '@/types/fari';

/** Housing silhouettes shared by the fitment stage and the schematic thumbnail. */
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

/** Radiator grille outlines, so the stage looks like the chosen make. */
export const GRILLES: Record<string, string> = {
    BMW: '<rect x="300" y="150" width="52" height="78" rx="16"/><rect x="368" y="150" width="52" height="78" rx="16"/>',
    Audi: '<path d="M296 146 h128 a10 10 0 0 1 10 10 l-10 66 a10 10 0 0 1 -10 10 h-108 a10 10 0 0 1 -10 -10 l-10 -66 a10 10 0 0 1 10 -10 z"/>',
    Volkswagen:
        '<rect x="286" y="158" width="148" height="16" rx="8"/><rect x="286" y="182" width="148" height="16" rx="8"/><rect x="286" y="206" width="148" height="16" rx="8"/>',
    Toyota: '<rect x="292" y="146" width="136" height="20" rx="10"/><path d="M286 182 h148 l-16 46 h-116 z"/>',
};

export const GRILLE_DEFAULT =
    '<rect x="292" y="152" width="136" height="74" rx="10"/>';

export const SIDES: Record<string, string> = {
    left: 'Левая',
    right: 'Правая',
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
