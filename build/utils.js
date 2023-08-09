import fs from "fs";
import {exec} from "child_process";
import {fileURLToPath} from "url";
import {dirname, join} from "path";
import crypto from "crypto";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const CD = `cd "${join(__dirname, "..")}"&&`;
const PHP = "\"" + join("./bin/php/php") + "\"";
const COMPOSER = "\"" + join("./composer.phar") + "\"";
const COMPOSER_HASH = "e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02";

export const runCommand = command => new Promise((res, rej) => exec(command, (error, stdout, stderr) => {
	if (error) return rej(error);
	if (stderr && !stderr.startsWith("> @php")) return rej(new Error(stderr));
	res();
}));

export function iniToJSON(file) {
	const content = fs.readFileSync(file, "utf8");
	const obj = {};
	content.split("\n")
		.filter(i => /[a-zA-Z]/.test(i[0]))
		.forEach(i => obj[i.split("=")[0].trim()] = i.split("=").slice(1).join("="));
	return obj;
}

export async function runPHP(code) {
	await runCommand(`${CD}${PHP} -r "${code.replaceAll("\n", "")}"`);
}

export async function buildPhar() {
	await runCommand(`${CD}${PHP} ${COMPOSER} make-server`);
}

export async function runSetup() {
	const COMPOSER_SETUP = join(__dirname, "../composer-setup.php");
	await runPHP(`copy("https://getcomposer.org/installer", "${COMPOSER_SETUP}");`);
	const hash = crypto.createHash("sha384")
		.update(fs.readFileSync(COMPOSER_SETUP))
		.digest().toString();
	if (hash !== COMPOSER_HASH) {
		fs.rmSync("composer-setup.php");
		throw "Composer setup file is corrupt.";
	}
	await runCommand(`${CD}${PHP} ${COMPOSER_SETUP}`);
	fs.rmSync("composer-setup.php");
	await runCommand(`${CD}${PHP} ${COMPOSER} install`);
}