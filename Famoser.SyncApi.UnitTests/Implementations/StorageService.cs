using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Services.Interfaces;

#pragma warning disable 1998
namespace Famoser.SyncApi.UnitTests.Implementations
{
    public class StorageService : IStorageService
    {
        private Dictionary<string, string> _cachedFiles = new Dictionary<string, string>();
        private Dictionary<string, byte[]> _cachedBytesFiles = new Dictionary<string, byte[]>();
        public void ClearCache()
        {
            _cachedFiles = new Dictionary<string, string>();
            _cachedBytesFiles = new Dictionary<string, byte[]>();
        }

        public int CountAllCachedFiles()
        {
            return _cachedFiles.Count + _cachedBytesFiles.Count;
        }

        public int CountAllRoamingFiles()
        {
            return _roamingFiles.Count + _roamingByteFiles.Count;
        }

        public async Task<string> GetCachedTextFileAsync(string filePath)
        {
            return _cachedFiles[filePath];
        }

        public async Task<bool> SetCachedTextFileAsync(string filePath, string content)
        {
            _cachedFiles[filePath] = content;
            return true;
        }

        public async Task<byte[]> GetCachedFileAsync(string filePath)
        {
            return _cachedBytesFiles[filePath];
        }

        public async Task<bool> SetCachedFileAsync(string filePath, byte[] content)
        {
            _cachedBytesFiles[filePath] = content;
            return true;
        }

        public async Task<bool> DeleteCachedFileAsync(string filePath)
        {
            return _cachedFiles.Remove(filePath) || _cachedBytesFiles.Remove(filePath);
        }

        private Dictionary<string, string> _roamingFiles = new Dictionary<string, string>();
        private Dictionary<string, byte[]> _roamingByteFiles = new Dictionary<string, byte[]>();
        public void CLearRoaming()
        {
            _roamingFiles = new Dictionary<string, string>();
            _roamingByteFiles = new Dictionary<string, byte[]>();
        }
        public async Task<string> GetRoamingTextFileAsync(string filePath)
        {
            return _roamingFiles[filePath];
        }

        public async Task<bool> SetRoamingTextFileAsync(string filePath, string content)
        {
            _roamingFiles[filePath] = content;
            return true;
        }

        public async Task<byte[]> GetRoamingFileAsync(string filePath)
        {
            return _roamingByteFiles[filePath];
        }

        public async Task<bool> SetRoamingFileAsync(string filePath, byte[] content)
        {
            _roamingByteFiles[filePath] = content;
            return true;
        }

        public async Task<bool> DeleteRoamingFileAsync(string filePath)
        {
            return _roamingFiles.Remove(filePath) || _roamingByteFiles.Remove(filePath);
        }

        public async Task<string> GetAssetTextFileAsync(string filePath)
        {
            return "";
        }

        public async Task<byte[]> GetAssetFileAsync(string filePath)
        {
            return new byte[1];
        }
    }
}
