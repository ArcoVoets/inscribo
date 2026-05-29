type TranslationParams = Record<string, string | number | boolean | null | undefined>;

export function translate(template: string, params: TranslationParams = {}): string {
    return template.replace(/:([A-Za-z0-9_]+)/g, (match, key: string) => {
        const value = params[key];

        return value == null ? match : String(value);
    });
}
