import {iniToJSON} from "../utils.js";
import {join} from "path";
import fs from "fs";

const NT = "§lNot translated: ";

export const name = "Translation fixes";

export default async function () {
	const files = fs.readdirSync(LOCALE_PATH).filter(i => i.endsWith(".ini"));
	for (const file of files) {
		const ini = file === MAIN_LOCALE_NAME ? MAIN_LOCALE : iniToJSON(join(LOCALE_PATH, file));
		let content = [];
		for (let key in MAIN_LOCALE) {
			key = key.trim();
			if (!key) continue;
			let value = ini[key] || MAIN_LOCALE[key];
			if (value.startsWith(NT)) value = MAIN_LOCALE[key];
			if (file !== MAIN_LOCALE_NAME && process.argv.includes("--translate") && value === MAIN_LOCALE[key]) {
				throw new Error("Fix this, requires an API key to work for a long time.");
				// const code = content.find(i => i[0] === "language.code")[1];
				// const translated = (await translate(value, "en", code)).translation;
				// content.push([key, translated]);
				// console.debug("+ " + file + ":" + key + ">" + translated);
			} else content.push([
				key,
				value === MAIN_LOCALE[key] && file !== MAIN_LOCALE_NAME ? NT + key + "§r " + value : value
			]);
		}
		content = content
			.filter(i => i[0])
			.map(i => `${i[0]}=${i[1]}`)
			.sort();
		content = [...content.filter(i => i.startsWith("language.")), "", ...content.filter(i => !i.startsWith("language."))];
		fs.writeFileSync(join(LOCALE_PATH, file), content.join("\n"));
	}
};