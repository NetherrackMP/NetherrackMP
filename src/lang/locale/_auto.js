import fs from "fs";
import {dirname, join} from "path";
import {fileURLToPath} from "url";
import {translate} from "bing-translate-api";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const T = Date.now();

const MAIN = "eng.ini";
const files = fs.readdirSync(__dirname).filter(i => i.endsWith(".ini"));

const iniToJSON = file => {
	const content = fs.readFileSync(join(__dirname, file), "utf8");
	const obj = {};
	content.split("\n")
		.filter(i => /[a-zA-Z]/.test(i[0]))
		.forEach(i => obj[i.split("=")[0].trim()] = i.split("=").slice(1).join("="));
	return obj;
};
const main = iniToJSON(MAIN);
const NT = "§lNot translated: ";

for (const file of files) {
	const ini = file === MAIN ? main : iniToJSON(file);
	let content = [];
	for (let key in main) {
		key = key.trim();
		if (!key) continue;
		let value = ini[key] || main[key];
		if (value.startsWith(NT)) value = main[key];
		if (file !== MAIN && process.argv.includes("--translate") && value === main[key]) {
			const code = content.find(i => i[0] === "language.code")[1];
			const translated = (await translate(value, "en", code)).translation;
			content.push([key, translated]);
			console.log("+ " + file + ":" + key + ">" + translated);
		} else content.push([key, value === main[key] && file !== MAIN ? NT + key : value]);
	}
	content = content
		.filter(i => i[0])
		.map(i => `${i[0]}=${i[1]}`)
		.sort();
	content = [...content.filter(i => i.startsWith("language.")), "", ...content.filter(i => !i.startsWith("language."))];
	fs.writeFileSync(join(__dirname, file), content.join("\n"));
	process.exit();
}

const DT = Date.now() - T;
console.log("Translations have been fixed in " + DT + "ms!");