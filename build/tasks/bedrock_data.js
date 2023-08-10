import {join} from "path";
import fs from "fs";

export const name = "Bedrock data files";

export default async function (exit) {
	const bedrockDataFolderPath = join(DIR, "../vendor/pocketmine/bedrock-data");
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

	fs.writeFileSync(join(DIR, "../src/data/bedrock/BedrockDataFiles.php"), content + "}");
};