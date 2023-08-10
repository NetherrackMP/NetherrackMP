import {join} from "path";
import fs from "fs";

export const name = "Known translation factory";

export default async function () {
	const factoryPath = join(DIR, "../src/lang/KnownTranslationFactory.php");
	const factoryCode = fs.readFileSync(factoryPath, "utf8");
	fs.writeFileSync(factoryPath, factoryCode.split("/*")[0] + "/**\n" +
		" * @internal\n" + Object.keys(MAIN_LOCALE).map(i => {
			if (!i) return "";
			const name = i.replaceAll(/[-.]/g, "_");
			const args = [...MAIN_LOCALE[i].matchAll(/\{%\d+}/g)].length;
			return ` * @method static Translatable ${name}(${[..." ".repeat(args)].map((_, j) => `Translatable|string $param${j}`).join(", ")})\n`;
		}).join("") + " */" + factoryCode.split("*/")[1]);
};