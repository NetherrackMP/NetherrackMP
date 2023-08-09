import fs from "fs";
import {dirname, join} from "path";
import {fileURLToPath} from "url";
// import {translate} from "bing-translate-api";
import {buildPhar, iniToJSON, runSetup} from "./utils.js";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const dir = join(__dirname, "../src/lang/locale");
let T = Date.now(), DT, ST = Date.now();

const MAIN = "en_us.ini";
const files = fs.readdirSync(dir).filter(i => i.endsWith(".ini"));

const main = iniToJSON(join(dir, MAIN));
const NT = "§lNot translated: ";

let TOTAL_TASKS = 4;
let DONE_TASKS = 0;
const completeTask = text => console.log(`(${++DONE_TASKS}/${TOTAL_TASKS}) ${text}`);
const exit = any => {
	console.log("Build failed at the " + (++DONE_TASKS) + ". task!");
	console.error(any);
	process.exit();
};


const composerPharPath = join(__dirname, "../composer.phar");
if (!fs.existsSync(composerPharPath)) {
	try {
		await runSetup();
	} catch (e) {
		exit(e);
	}
	TOTAL_TASKS++;
	DT = Date.now() - T;
	completeTask("Installed composer (" + DT + "ms)");
	T = Date.now();
} else if (!fs.statSync(composerPharPath).isFile()) exit("Expected composer.phar to be a file!");


for (const file of files) {
	const ini = file === MAIN ? main : iniToJSON(join(dir, file));
	let content = [];
	for (let key in main) {
		key = key.trim();
		if (!key) continue;
		let value = ini[key] || main[key];
		if (value.startsWith(NT)) value = main[key];
		if (file !== MAIN && process.argv.includes("--translate") && value === main[key]) {
			throw new Error("Fix this, requires an API key to work for a long time.");
			// const code = content.find(i => i[0] === "language.code")[1];
			// const translated = (await translate(value, "en", code)).translation;
			// content.push([key, translated]);
			// console.debug("+ " + file + ":" + key + ">" + translated);
		} else content.push([key, value === main[key] && file !== MAIN ? NT + key + "§r " + value : value]);
	}
	content = content
		.filter(i => i[0])
		.map(i => `${i[0]}=${i[1]}`)
		.sort();
	content = [...content.filter(i => i.startsWith("language.")), "", ...content.filter(i => !i.startsWith("language."))];
	fs.writeFileSync(join(dir, file), content.join("\n"));
}

DT = Date.now() - T;
completeTask("Translation fixes (" + DT + "ms)");
T = Date.now();


const factoryPath = join(__dirname, "../src/lang/KnownTranslationFactory.php");
const factoryCode = fs.readFileSync(factoryPath, "utf8");
fs.writeFileSync(factoryPath, factoryCode.split("/*")[0] + "/**\n" +
	" * @internal\n" + Object.keys(main).map(i => {
		if (!i) return "";
		const name = i.replaceAll(/[-.]/g, "_");
		const args = [...main[i].matchAll(/\{%\d+}/g)].length;
		return ` * @method static Translatable ${name}(${[..." ".repeat(args)].map((_, j) => `Translatable|string $param${j}`).join(", ")})\n`;
	}).join("") + " */" + factoryCode.split("*/")[1]);

DT = Date.now() - T;
completeTask("KnownTranslationFactory.php (" + DT + "ms)");
T = Date.now();


const bedrockDataFolderPath = join(__dirname, "../vendor/pocketmine/bedrock-data");
if (!fs.existsSync(bedrockDataFolderPath) || !fs.statSync(bedrockDataFolderPath).isDirectory())
	exit("File not found: " + bedrockDataFolderPath);

let content = `<?php

declare(strict_types=1);

namespace pocketmine\\data\\bedrock;

use const pocketmine\\BEDROCK_DATA_PATH;

final class BedrockDataFiles
{
\tprivate function __construct()
\t{
\t}

`;
for (const file of fs.readdirSync(bedrockDataFolderPath)) {
	const p = join(bedrockDataFolderPath, file);
	if (
		!fs.statSync(p).isFile() ||
		["README.md", "LICENSE", "composer.json"].includes(file) ||
		file.startsWith(".")
	) continue;
	content += `\tpublic const ${file.toUpperCase().replaceAll(/[-.]/g, "_")} = BEDROCK_DATA_PATH . "${file}";\n`
}

fs.writeFileSync(join(__dirname, "../src/data/bedrock/BedrockDataFiles.php"), content + "}");

DT = Date.now() - T;
completeTask("BedrockDataFiles.php (" + DT + "ms)");
T = Date.now();


await buildPhar();
DT = Date.now() - T;
completeTask("Netherrack-MP.phar (" + DT + "ms)");
T = Date.now();


DT = Date.now() - ST;
console.log("Everything is built (" + DT + "ms)");